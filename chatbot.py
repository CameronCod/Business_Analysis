# chatbot.py
from flask import Flask, render_template, request, jsonify
import pandas as pd
import re

app = Flask(__name__)

# === STEP 1: Load & Clean CSV Data ===
bookings_df = pd.read_csv("hotel_bookings.csv")
reviews_df = pd.read_csv("hotel_reviews.csv")
booking_df = pd.read_csv("hotel_booking.csv")

def clean_columns(df):
    df.columns = (
        df.columns.str.strip()
                  .str.lower()
                  .str.replace(' ', '_')
                  .str.replace('[^0-9a-zA-Z_]', '', regex=True)
    )
    return df

bookings_df = clean_columns(bookings_df)
reviews_df = clean_columns(reviews_df)
booking_df = clean_columns(booking_df)

# === STEP 2: Chatbot Logic ===
def chatbot_response(user_input: str) -> str:
    text = user_input.lower()

    # Total bookings
    if re.search(r'\b(total|how many)\b.*\bbookings?\b', text):
        total = len(bookings_df)
        return f"There are {total:,} total bookings in the dataset."

    # Total reviews
    if re.search(r'\b(total|how many)\b.*\breviews?\b', text):
        total = len(reviews_df)
        return f"There are {total:,} hotel reviews available."

    # Bookings by country
    match_country = re.search(r'bookings.*from\s+([a-zA-Z\s]+)', text)
    if match_country:
        country = match_country.group(1).strip().title()
        if 'country' in bookings_df.columns:
            count = len(bookings_df[bookings_df['country'].str.strip().str.title() == country])
            return f"There are {count:,} bookings from {country}."
        else:
            return "Sorry, I couldn't find a 'country' column in the dataset."

    # Average lead time
    if re.search(r'average.*lead.*time', text):
        if 'lead_time' in bookings_df.columns:
            avg_lead = bookings_df['lead_time'].mean()
            return f"The average lead time for bookings is {avg_lead:.2f} days."
        else:
            return "Lead time data not found."

    # Cancelled bookings
    if re.search(r'cancel', text):
        if 'is_canceled' in bookings_df.columns:
            canceled = bookings_df['is_canceled'].sum()
            total = len(bookings_df)
            percent = (canceled / total) * 100
            return f"{canceled:,} bookings were cancelled ({percent:.1f}%)."
        else:
            return "Cancellation data not found."

    # Top reviewed hotel
    if re.search(r'(most|top).*reviews?', text):
        if 'hotel' in reviews_df.columns:
            top_hotel = reviews_df['hotel'].value_counts().idxmax()
            top_count = reviews_df['hotel'].value_counts().max()
            return f"The hotel with the most reviews is '{top_hotel}' with {top_count:,} reviews."
        else:
            return "No 'hotel' column in reviews dataset."

    # Fallback
    return "I'm not sure how to answer that. Try asking about total bookings, reviews, countries, cancellations, etc."

# === STEP 3: Flask Routes ===
@app.route("/")
def index():
    return render_template("index.html")

@app.route("/ask", methods=["POST"])
def ask():
    data = request.get_json()
    user_message = data.get("message", "")
    bot_reply = chatbot_response(user_message)
    return jsonify({"reply": bot_reply})

if __name__ == "__main__":
    app.run(debug=True)
