import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.preprocessing import LabelEncoder
import json
import sys
import joblib
import os
from datetime import datetime, timedelta

# --- Configuration ---
MODEL_DIR = 'ai_models'
MODEL_PATH = os.path.join(MODEL_DIR, 'pricing_model.pkl')
ENCODERS_PATH = os.path.join(MODEL_DIR, 'pricing_encoders.pkl')
TRAINING_DATA_PATH = 'Reservations.csv'

class DynamicPricingModel:
    def __init__(self):
        self.model = None
        # Updated features to match CSV column names (e.g., room_type -> room_type_reserved)
        self.features = [
            'room_type_reserved', 'arrival_month', 'arrival_date', 'lead_time', 
            'no_of_weekend_nights', 'no_of_week_nights', 'no_of_adults',
            'no_of_children', 'market_segment_type', 'no_of_special_requests'
        ]
        self.categorical_features = ['room_type_reserved', 'market_segment_type']
        self.encoders = {}
        
    def train_from_csv(self, csv_file_path):
        """Load data from CSV, preprocess, and train the pricing model."""
        print(f"Loading data from {csv_file_path} for training...")
        try:
            df = pd.read_csv(csv_file_path)
        except FileNotFoundError:
            print(f"Error: Training file '{csv_file_path}' not found. Cannot train model.")
            return

        # 1. Data Cleaning and Filtering
        # Use only 'Not_Canceled' bookings for training the price model
        df = df[df['booking_status'] == 'Not_Canceled'].copy()
        # Filter out unusually low prices (errors or complimentary stays)
        df = df[df['avg_price_per_room'] > 1.0].copy()
        
        # Ensure required features are present
        if not all(feature in df.columns for feature in self.features + ['avg_price_per_room']):
            print("Error: Required columns are missing in the training data. Check CSV headers.")
            return

        X = df[self.features].copy()
        y = df['avg_price_per_room'].copy()
        
        # 2. Encode categorical variables
        for column in self.categorical_features:
            self.encoders[column] = LabelEncoder()
            # Explicitly use .loc for assignment to avoid SettingWithCopyWarning
            X.loc[:, column] = self.encoders[column].fit_transform(X[column].astype(str).fillna('missing'))
        
        # 3. Train model
        print("Training RandomForestRegressor model...")
        self.model = RandomForestRegressor(n_estimators=150, max_depth=10, random_state=42, n_jobs=-1)
        self.model.fit(X, y)
        
        # 4. Save model and encoders
        os.makedirs(MODEL_DIR, exist_ok=True)
        joblib.dump(self.model, MODEL_PATH)
        joblib.dump(self.encoders, ENCODERS_PATH)
        print(f"Training complete. Model and encoders saved to {MODEL_DIR}/.")
        
    def predict_optimal_price(self, room_data):
        """Predict optimal price for a room using the trained ML model and business logic adjustments."""
        
        if self.model is None:
            self.load_model()
            
        base_price_fallback = self.get_base_price(room_data)

        if self.model is None:
            # If model loading failed, use static price with dynamic adjustments as fallback
            print("Warning: Model not trained/loaded. Falling back to static base price + adjustments.")
            return self.apply_dynamic_adjustments(base_price_fallback, room_data)
        
        # Prepare input for model prediction
        input_data = {k: room_data.get(k) for k in self.features}
        input_df = pd.DataFrame([input_data])
        
        # Encode categorical variables for prediction
        for column in self.categorical_features:
            if column in self.encoders:
                encoder = self.encoders[column]
                category = str(input_data.get(column)) # Convert to string to match training
                
                # Handle unseen categories by checking if the category exists in the encoder's classes
                if category not in encoder.classes_:
                    print(f"Warning: Category '{category}' for '{column}' not seen during training. Using fallback index 0.")
                    input_df.loc[:, column] = 0
                else:
                    input_df.loc[:, column] = encoder.transform([category])[0]
        
        # Make base ML prediction
        try:
            base_ml_price = self.model.predict(input_df[self.features])[0]
        except Exception as e:
            print(f"Prediction failed with trained model: {e}. Using static base price fallback.")
            base_ml_price = base_price_fallback

        # Apply dynamic adjustments to the model's base price
        final_price = self.apply_dynamic_adjustments(base_ml_price, room_data)
        
        # Ensure the final price is never below the static base price
        return max(final_price, base_price_fallback)
    
    def apply_dynamic_adjustments(self, base_price, room_data):
        """Apply real-time dynamic pricing adjustments (business logic)."""
        adjustments = 0
        
        arrival_month = room_data.get('arrival_month', 1)
        weekend_nights = room_data.get('no_of_weekend_nights', 0)
        lead_time = room_data.get('lead_time', 100)
        special_requests = room_data.get('no_of_special_requests', 0)
        
        # Weekend premium (15%)
        if weekend_nights > 0:
            adjustments += base_price * 0.15
        
        # High demand season (months 6-8, 10-12) (20%)
        if arrival_month in [6, 7, 8, 10, 11, 12]:
            adjustments += base_price * 0.20
        
        # Last-minute booking premium (lead time < 7 days) (10%)
        if lead_time < 7:
            adjustments += base_price * 0.10
        
        # Special requests premium (more than 1 request) (5%)
        if special_requests > 1:
            adjustments += base_price * 0.05
        
        return base_price + adjustments
    
    def get_base_price(self, room_data):
        """Get static base price for room type (fallback/minimum price)."""
        room_type = room_data.get('room_type_reserved', 'Unknown')
        base_prices = {
            'Room_Type 1': 65.00,
            'Room_Type 2': 82.00,
            'Room_Type 4': 105.00,
            'Room_Type 5': 113.00,
            'Room_Type 6': 185.00,
            'Room_Type 7': 215.00
        }
        return base_prices.get(room_type, 100.00)
    
    def load_model(self):
        """Load trained model and encoders."""
        try:
            self.model = joblib.load(MODEL_PATH)
            self.encoders = joblib.load(ENCODERS_PATH)
            print("Model and encoders loaded successfully.")
        except FileNotFoundError:
            self.model = None
            self.encoders = {}
            print("Warning: Model files not found. Please run with 'train' argument first.")
        except Exception as e:
            self.model = None
            self.encoders = {}
            print(f"Error loading model files: {e}")

def main():
    pricing_model = DynamicPricingModel()
    args = sys.argv[1:]
    
    if len(args) == 1 and args[0] == 'train':
        pricing_model.train_from_csv(TRAINING_DATA_PATH)
        return
    
    if len(args) == 2:
        input_file = args[0]
        output_file = args[1]
        
        # Auto-train if model is missing
        if not os.path.exists(MODEL_PATH) or not os.path.exists(ENCODERS_PATH):
            print("Model files not found. Attempting to train using 'Reservations.csv'...")
            pricing_model.train_from_csv(TRAINING_DATA_PATH)
            
        try:
            with open(input_file, 'r') as f:
                room_data = json.load(f)
            
            # Key Mapping for Prediction Input (to match internal feature names)
            # This handles if the input JSON still uses the old, shorter names
            if 'room_type' in room_data and 'room_type_reserved' not in room_data:
                room_data['room_type_reserved'] = room_data.pop('room_type')
            if 'market_segment' in room_data and 'market_segment_type' not in room_data:
                room_data['market_segment_type'] = room_data.pop('market_segment')

            optimal_price = pricing_model.predict_optimal_price(room_data)
            base_price = pricing_model.get_base_price(room_data)

            premium_percentage = 0.0
            if base_price > 0:
                premium_percentage = round(((optimal_price / base_price) - 1) * 100, 1)

            result = {
                "optimal_price": round(optimal_price, 2),
                "base_price": round(base_price, 2),
                "premium_percentage": premium_percentage,
                "confidence": 0.85
            }
            
            with open(output_file, 'w') as f:
                json.dump(result, f, indent=4)
                
            print(f"Prediction successful. Result written to {output_file}")
            
        except Exception as e:
            error_result = {"optimal_price": 100.00, "base_price": 100.00, "premium_percentage": 0.0, "error": str(e)}
            with open(output_file, 'w') as f:
                json.dump(error_result, f, indent=4)
            print(f"Error during prediction: {e}. Default fallback price written to {output_file}")

    else:
        usage = f"Usage: \n\n" \
                f"1. Training: python {os.path.basename(sys.argv[0])} train\n" \
                f"2. Prediction: python {os.path.basename(sys.argv[0])} <input_json> <output_json>\n"
        print(usage)
        sys.exit(1)

if __name__ == "__main__":
    main()
