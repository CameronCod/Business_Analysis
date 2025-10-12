import pandas as pd
from sklearn.ensemble import RandomForestClassifier
import json
import sys
import joblib
import os

# --- Configuration ---
MODEL_DIR = 'ai_models'
MODEL_PATH = os.path.join(MODEL_DIR, 'maintenance_model.pkl')
TRAINING_DATA_PATH = 'Reservations.csv'
# Using the same configuration style as dynamic_pricing_model.py, 
# although encoders are not strictly needed here since we are using numerical features.

class PredictiveMaintenance:
    def __init__(self):
        self.model = None
        # Features from Reservations.csv used as proxies for room stress/wear-and-tear
        self.features = [
            'total_nights', 'total_guests', 'avg_price_per_room', 'no_of_special_requests'
        ]
        # Store calculated thresholds (used for target creation and fallback heuristic)
        self.thresholds = {} 
        
    def _create_synthetic_features(self, df):
        """Creates calculated features from raw booking data."""
        df['total_nights'] = df['no_of_weekend_nights'] + df['no_of_week_nights']
        df['total_guests'] = df['no_of_adults'] + df['no_of_children']
        return df

    def train_model(self, csv_file_path):
        """Train maintenance prediction model using booking data as a proxy for wear-and-tear."""
        print(f"Loading data from {csv_file_path} for training...")
        try:
            df = pd.read_csv(csv_file_path)
        except FileNotFoundError:
            print(f"Error: Training file '{csv_file_path}' not found. Cannot train model.")
            return

        # 1. Data Cleaning and Feature Engineering
        df = df[df['booking_status'] == 'Not_Canceled'].copy()
        df = self._create_synthetic_features(df)
        
        # 2. Create Synthetic Target Variable (Proxy for high room stress/maintenance need)
        # Maintenance is 'required' for bookings that represent high utilization:
        # - total nights in top 25% quantile
        # - high total guests (> 3)
        # - high avg price (top 25%)
        self.thresholds['nights_75q'] = df['total_nights'].quantile(0.75)
        self.thresholds['price_75q'] = df['avg_price_per_room'].quantile(0.75)
        
        df['maintenance_required'] = (
            (df['total_nights'] > self.thresholds['nights_75q']) & 
            (df['total_guests'] >= 3) & 
            (df['avg_price_per_room'] > self.thresholds['price_75q']) &
            (df['no_of_special_requests'] >= 1)
        )
        
        X = df[self.features]
        y = df['maintenance_required']
        
        # 3. Train model
        print("Training RandomForestClassifier model...")
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.model.fit(X, y)
        
        # 4. Save model and thresholds
        os.makedirs(MODEL_DIR, exist_ok=True)
        joblib.dump(self.model, MODEL_PATH)
        joblib.dump(self.thresholds, os.path.join(MODEL_DIR, 'maintenance_thresholds.pkl'))
        print(f"Training complete. Model saved to {MODEL_DIR}/.")

    def predict_maintenance(self, room_stats):
        """Predict maintenance needs for a room based on booking stats."""
        if self.model is None:
            self.load_model()
            
        if self.model is None:
            print("Warning: Model not trained/loaded. Falling back to heuristic prediction.")
            return self.heuristic_prediction(room_stats)
        
        # 1. Prepare input and create synthetic features
        input_df = pd.DataFrame([room_stats])
        input_df = self._create_synthetic_features(input_df)

        # Ensure all required prediction features are present
        if not all(f in input_df.columns for f in self.features):
            print("Error: Input data missing required features after processing.")
            return self.heuristic_prediction(room_stats)
            
        # 2. Make prediction
        try:
            # Predict probability of needing maintenance (class 1)
            probability = self.model.predict_proba(input_df[self.features])[0][1]
        except Exception as e:
            print(f"Prediction failed with trained model: {e}. Using heuristic fallback.")
            return self.heuristic_prediction(room_stats)
        
        return {
            "needs_maintenance": probability > 0.65, # Lower threshold for more proactive maintenance
            "probability": round(probability, 3),
            "urgency": "high" if probability > 0.8 else "medium" if probability > 0.65 else "low",
            "recommended_action": self.get_recommendation(probability, room_stats)
        }
    
    def heuristic_prediction(self, room_stats):
        """Fallback heuristic prediction based on input utilization without ML model."""
        # Using simplified metrics since the actual historical context is unavailable
        
        # Create synthetic features for heuristic calculation
        temp_df = self._create_synthetic_features(pd.DataFrame([room_stats]))
        total_nights = temp_df['total_nights'].iloc[0]
        total_guests = temp_df['total_guests'].iloc[0]
        special_requests = room_stats.get('no_of_special_requests', 0)
        
        risk_score = 0.0
        
        # High utilization increases risk
        if total_nights >= 5:
            risk_score += 0.3
        if total_guests >= 4:
            risk_score += 0.3
        if special_requests >= 2:
            risk_score += 0.2
            
        # If the ML thresholds are loaded, use them for a more informed heuristic
        if 'nights_75q' in self.thresholds and total_nights > self.thresholds['nights_75q']:
            risk_score += 0.4
        
        risk_score = min(risk_score, 1.0) # Cap score at 1.0
            
        return {
            "needs_maintenance": risk_score > 0.6,
            "probability": round(risk_score, 3),
            "urgency": "high" if risk_score > 0.8 else "medium" if risk_score > 0.6 else "low",
            "recommended_action": "Routine inspection" if risk_score < 0.6 else "Maintenance required"
        }
    
    def get_recommendation(self, probability, room_stats):
        """Get maintenance recommendation based on prediction probability."""
        if probability > 0.8:
            return "Immediate maintenance required - high predicted stress/wear-and-tear"
        elif probability > 0.65:
            return "Schedule maintenance within next 7 days"
        elif probability > 0.4:
            return "Routine inspection recommended before next long stay"
        else:
            return "No immediate action needed"
    
    def load_model(self):
        """Load trained model and thresholds."""
        try:
            self.model = joblib.load(MODEL_PATH)
            # Load thresholds, which are needed for the heuristic fallback if the ML model fails
            self.thresholds = joblib.load(os.path.join(MODEL_DIR, 'maintenance_thresholds.pkl'))
            print("Maintenance model and thresholds loaded successfully.")
        except FileNotFoundError:
            self.model = None
            self.thresholds = {}
            print("Warning: Model files not found. Please run with 'train' argument first.")
        except Exception as e:
            self.model = None
            self.thresholds = {}
            print(f"Error loading model files: {e}")

def main():
    pricing_model = PredictiveMaintenance()
    args = sys.argv[1:]
    
    if len(args) == 1 and args[0] == 'train':
        pricing_model.train_model(TRAINING_DATA_PATH)
        return
    
    if len(args) == 2:
        input_file = args[0]
        output_file = args[1]
        
        # Auto-train if model is missing
        if not os.path.exists(MODEL_PATH):
            print("Model files not found. Attempting to train using 'Reservations.csv'...")
            pricing_model.train_model(TRAINING_DATA_PATH)
            
        try:
            with open(input_file, 'r') as f:
                room_data = json.load(f)
            
            # Since this model is based on booking metrics, we map the input to the required metrics.
            # We assume the input JSON will contain the required booking metrics (e.g., no_of_adults).
            # No special key mapping is required here, as the input keys should match the features 
            # used to calculate synthetic features like 'total_nights'.

            prediction = pricing_model.predict_maintenance(room_data)
            
            with open(output_file, 'w') as f:
                json.dump(prediction, f, indent=4)
                
            print(f"Prediction successful. Result written to {output_file}")
            
        except Exception as e:
            error_result = {"needs_maintenance": False, "probability": 0.0, "urgency": "low", "error": str(e)}
            with open(output_file, 'w') as f:
                json.dump(error_result, f, indent=4)
            print(f"Error during prediction: {e}. Default fallback result written to {output_file}")

    else:
        usage = f"Usage: \n\n" \
                f"1. Training: python {os.path.basename(sys.argv[0])} train\n" \
                f"2. Prediction: python {os.path.basename(sys.argv[0])} <input_json> <output_json>\n"
        print(usage)
        

if __name__ == "__main__":
    main()
