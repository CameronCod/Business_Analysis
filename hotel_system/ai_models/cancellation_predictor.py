import pandas as pd
import numpy as np
import json
import sys
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder
import joblib
import os

class CancellationPredictor:
    def __init__(self):
        self.model = None
        self.encoders = {}
        self.features = ['lead_time', 'no_of_adults', 'no_of_children', 
                        'no_of_weekend_nights', 'no_of_week_nights', 
                        'required_car_parking_space', 'avg_price_per_room', 
                        'no_of_special_requests', 'arrival_month']
    
    def train_model(self, data):
        # Convert to DataFrame
        df = pd.DataFrame(data)
        
        # Prepare features
        X = df[self.features]
        y = df['booking_status'].apply(lambda x: 1 if x == 'Canceled' else 0)
        
        # Train model
        self.model = RandomForestClassifier(n_estimators=100, random_state=42)
        self.model.fit(X, y)
        
        # Save model
        joblib.dump(self.model, 'ai_models/cancellation_model.pkl')
    
    def predict(self, input_data):
        if self.model is None and os.path.exists('ai_models/cancellation_model.pkl'):
            self.model = joblib.load('ai_models/cancellation_model.pkl')
        
        if self.model is None:
            return {"probability": 0.5, "confidence": 0.0}
        
        # Prepare input
        input_df = pd.DataFrame([input_data])
        input_features = input_df[self.features]
        
        # Make prediction
        probability = self.model.predict_proba(input_features)[0][1]
        confidence = abs(probability - 0.5) * 2
        
        return {
            "probability": float(probability),
            "confidence": float(confidence),
            "risk_level": "high" if probability > 0.7 else "medium" if probability > 0.4 else "low"
        }

def main():
    if len(sys.argv) != 3:
        print(json.dumps({"error": "Invalid arguments"}))
        return
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    try:
        with open(input_file, 'r') as f:
            input_data = json.load(f)
        
        predictor = CancellationPredictor()
        result = predictor.predict(input_data)
        
        with open(output_file, 'w') as f:
            json.dump(result, f)
            
    except Exception as e:
        error_result = {"probability": 0.5, "confidence": 0.0, "error": str(e)}
        with open(output_file, 'w') as f:
            json.dump(error_result, f)

if __name__ == "__main__":
    main()