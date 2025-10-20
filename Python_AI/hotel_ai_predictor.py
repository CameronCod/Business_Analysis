import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score
import numpy as np

# CANCELLATION PREDICTION 
def train_cancellation_model(file_path):
    """Loads data, preprocesses it, and trains a Logistic Regression model to predict cancellations."""
    try:
        
        df = pd.read_csv(file_path)
    except FileNotFoundError:
        print(f"\nERROR: The file '{file_path}' was not found.")
        return None, None
    
    
    df.rename(columns={
        'no_of_adults': 'adults',
        'no_of_children': 'children',
        'no_of_weekend_nights': 'stays_in_weekend_nights',
        'no_of_week_nights': 'stays_in_week_nights',
        'avg_price_per_room': 'adr',
        'required_car_parking_space': 'required_car_parking_spaces',
        'repeated_guest': 'is_repeated_guest',
        'no_of_special_requests': 'total_of_special_requests',
        'market_segment_type': 'market_segment',
        'type_of_meal_plan': 'meal_plan' 
    }, inplace=True)
    

    df['is_canceled'] = df['booking_status'].apply(lambda x: 1 if x == 'Canceled' else 0)

    df.dropna(subset=['adr'], inplace=True)

    df = df[(df['adults'] > 0) | (df['children'] > 0)]
    
 
    df['total_nights'] = df['stays_in_weekend_nights'] + df['stays_in_week_nights']
    
    
    features = [
        'lead_time', 'total_nights', 'adults', 'children', 
        'required_car_parking_spaces', 'total_of_special_requests', 
        'is_repeated_guest', 'meal_plan', 'market_segment'
    ]

    df_model = pd.get_dummies(df, columns=['meal_plan', 'market_segment'], drop_first=True)
    
    X_cols = ['lead_time', 'total_nights', 'adults', 'children', 
              'required_car_parking_spaces', 'total_of_special_requests', 
              'is_repeated_guest'] + [col for col in df_model.columns if col.startswith(('meal_plan_', 'market_segment_'))]

    X_cols = list(set(X_cols) & set(df_model.columns))

    X = df_model[X_cols]
    y = df_model['is_canceled']
    

    X = X.fillna(0)

   
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    
    model = LogisticRegression(max_iter=5000, solver='lbfgs')
    model.fit(X_train, y_train)
    
    # Evaluate the model
    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    
    print(f"-> Cancellation Prediction Model Trained! (Accuracy: {accuracy:.2f})")
    
    return model, X_cols

# REVENUE ANALYSIS FUNCTION 
def calculate_revenue_metrics(df):
    """Calculates and prints key revenue metrics."""
    
    if df is None:
        return
        
    df = df.copy()
    
    
    df.rename(columns={
        'no_of_weekend_nights': 'stays_in_weekend_nights',
        'no_of_week_nights': 'stays_in_week_nights',
        'avg_price_per_room': 'adr',
        'booking_status': 'is_canceled', 
        'market_segment_type': 'market_segment' 
    }, inplace=True)
    
    
    df['is_canceled'] = df['is_canceled'].apply(lambda x: 1 if x == 'Canceled' else 0)
        
    df.dropna(subset=['adr'], inplace=True)
    df['total_nights'] = df['stays_in_weekend_nights'] + df['stays_in_week_nights']
    
    
    df['potential_revenue'] = df['adr'] * df['total_nights']
    df['realized_revenue'] = np.where(df['is_canceled'] == 0, df['potential_revenue'], 0)
    
    total_potential = df['potential_revenue'].sum()
    total_realized = df['realized_revenue'].sum()
    
    print("\n\n--- 💸 Revenue Analysis ---")
    print(f"Total Bookings: {len(df):,}")
    print(f"Total Potential Revenue (ADR * Nights): ${total_potential:,.2f}")
    print(f"Total Realized Revenue (Non-Canceled): ${total_realized:,.2f}")
    print(f"Cancellation Rate: {df['is_canceled'].mean() * 100:.2f}%")
    
    
    revenue_by_segment = df.groupby('market_segment')['realized_revenue'].sum()
    print("\nRealized Revenue by Market Segment:")
    print(revenue_by_segment.to_string())

#  HOUSEKEEPING LOGIC 
def housekeeping_scheduler(rooms_status):
    """Determines room cleaning priority based on status (simulated operational logic)."""
    to_clean_priority = []
    
    for room, status in rooms_status.items():
        if status == 'Check-Out':
            
            to_clean_priority.append((1, room, 'Full Turnover')) 
        elif status == 'Stay-Over':
            
            to_clean_priority.append((2, room, 'Daily Service')) 
        elif status == 'New-Check-In':
            
            to_clean_priority.append((3, room, 'Inspect/Prep'))
            
    
    to_clean_priority.sort(key=lambda x: x[0])
    
    print("\n\n--- 🧹 Housekeeping Schedule ---")
    if not to_clean_priority:
        print("All simulated rooms are Clean & Ready.")
        return
        
    for priority, room, task in to_clean_priority:
        print(f"[Priority {priority}] Room {room}: {task}")

#  REVIEWS/SENTIMENT ANALYSIS LOGIC 
def analyze_review_sentiment(review_text):
    """Simple keyword-based sentiment analysis (simulating NLP)."""
    positive_words = ['great', 'excellent', 'amazing', 'friendly', 'love', 'perfect', 'best', 'clean']
    negative_words = ['poor', 'terrible', 'awful', 'slow', 'dirty', 'problem', 'disappointing', 'worst']
    
    score = 0
    words = review_text.lower().split()
    
    for word in words:
        if word in positive_words:
            score += 1
        elif word in negative_words:
            score -= 1
            
    print("\n\n---  Review Sentiment Analysis ---")
    print(f"Review: '{review_text}'")
    if score > 0:
        print(f"Sentiment: Positive (Score: +{score}) - Suggests High Guest Satisfaction!")
    elif score < 0:
        print(f"Sentiment: Negative (Score: {score}) - Suggests areas for Improvement.")
    else:
        print("Sentiment: Neutral/Unclear (Score: 0)")
        

def run_hospitality_ai_system():
    """Main function to run the terminal application."""
    
    
    DATA_FILE = 'Reservations.csv' 
    
    
    global hotel_cancellation_model
    global model_features
    
    
    try:
        df_full = pd.read_csv(DATA_FILE)
    except Exception as e:
        print(f"FATAL ERROR: Could not load data. Ensure '{DATA_FILE}' is in the correct directory. Details: {e}")
        return

    
    hotel_cancellation_model, model_features = train_cancellation_model(DATA_FILE)
    
    if hotel_cancellation_model is None:
        return

    
    SIMULATED_ROOMS = {
        '101': 'Check-Out',
        '102': 'Stay-Over',
        '103': 'Clean & Ready',
        '201': 'Check-Out',
        '202': 'Stay-Over',
        '203': 'New-Check-In'
    }

    while True:
        print("\n" + "="*50)
        print(" AI HOTEL MANAGEMENT SYSTEM (Pure Python Terminal)")
        print("="*50)
        print("1. AI Cancellation Prediction")
        print("2. Revenue Performance Analysis")
        print("3. Housekeeping Scheduling")
        print("4. Guest Review Sentiment Analysis")
        print("5. Exit")
        
        choice = input("Enter your choice (1-5): ")
        
        if choice == '1':
            print("\n---  AI Cancellation Prediction ---")
            print("Predict if a new booking will be canceled based on a few key metrics.")
            
            try:
                
                new_booking = {
                    'lead_time': int(input("Lead Time (days before arrival, e.g., 50): ")),
                    'total_nights': int(input("Total Nights (e.g., 5): ")),
                    'adults': int(input("Number of Adults (e.g., 2): ")),
                    'children': int(input("Number of Children (e.g., 0): ")),
                    'required_car_parking_spaces': int(input("Car Parking Space Required (0 or 1): ")),
                    'total_of_special_requests': int(input("Total Special Requests (e.g., 1): ")),
                    'is_repeated_guest': int(input("Is Repeated Guest (0 or 1): ")),
                    
                    'meal_plan_Meal Plan 2': int(input("Is Meal Plan 2 Selected (0 or 1): ")),
                    'market_segment_Online': int(input("Is Market Segment 'Online' (0 or 1): ")),
                    
                }
                
                
                predict_df = pd.DataFrame(columns=model_features)
                predict_df.loc[0] = 0
                
                
                for key, value in new_booking.items():
                    if key in model_features:
                        predict_df.loc[0, key] = value
                
                
                predict_df = predict_df.fillna(0)
                
                
                prediction = hotel_cancellation_model.predict(predict_df)
                
                prob_index = list(hotel_cancellation_model.classes_).index(1)
                probability = hotel_cancellation_model.predict_proba(predict_df)[0][prob_index]
                
                print("\n--- AI Prediction Result ---")
                if prediction[0] == 1:
                    print(f" **HIGH RISK**: Predicted to be **Canceled** (Probability: {probability*100:.1f}%)")
                    print("Action: Consider offering a personalized discount or communication.")
                else:
                    print(f" **LOW RISK**: Predicted **Not Canceled** (Probability: {probability*100:.1f}%)")
                    print("Action: Proceed with standard pre-arrival communication.")
                    
            except ValueError:
                print("Invalid input. Please enter numbers for the required fields.")
            except Exception as e:
                 print(f"An error occurred during prediction: {e}")
                 
        elif choice == '2':
            
            try:
                df_full = pd.read_csv(DATA_FILE)
                calculate_revenue_metrics(df_full)
            except Exception as e:
                print(f"Error during Revenue Analysis: {e}")
            
        elif choice == '3':
            housekeeping_scheduler(SIMULATED_ROOMS)
            print("\n*Note: This is simulated logic. In a real system, status would update from check-out events and a mobile app.*")
            
        elif choice == '4':
            review = input("\nEnter a guest review to analyze (e.g., 'The room was dirty and service was slow'): ")
            analyze_review_sentiment(review)
            
        elif choice == '5':
            print("\nExiting AI Hotel Management System. Goodbye!")
            break
            
        else:
            print("Invalid choice. Please enter a number between 1 and 5.")

if __name__ == "__main__":
    
    pd.options.display.float_format = '{:,.2f}'.format
    np.set_printoptions(suppress=True, precision=2)
    run_hospitality_ai_system()