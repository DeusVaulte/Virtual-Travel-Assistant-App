from flask import Flask, request, jsonify
from flask_cors import CORS
import pandas as pd
import random
import pymysql
import traceback  # Add this at the top

# Define DestinationRecommender class
class DestinationRecommender:
    def __init__(self, destinations, learning_rate=0.1, discount_factor=0.9, exploration_rate=0.2):
        self.destinations = destinations
        self.q_table = {dest: 0 for dest in destinations}  # Q-values for destinations
        self.lr = learning_rate
        self.gamma = discount_factor
        self.epsilon = exploration_rate

    def suggest_destination(self, accommodation_budget, transportation_budget, transportation_type, climate, activity):
        if random.random() < self.epsilon:
            return random.choice(self.destinations)  # Exploration
        else:
            return max(self.q_table, key=self.q_table.get)  # Exploitation

    def update_q_table(self, destination, reward):
        current_q = self.q_table[destination]
        self.q_table[destination] = current_q + self.lr * (reward + self.gamma * max(self.q_table.values()) - current_q)

# Load dataset
csv_file = 'Cleaned_Travel_Dataset.csv'
df = pd.read_csv(csv_file)

# Convert cost columns to float after removing currency symbols (if needed)
df['Accommodation cost'] = df['Accommodation cost'].replace('[\$,USD]', '', regex=True).astype(float)
df['Transportation cost'] = df['Transportation cost'].replace('[\$,USD]', '', regex=True).astype(float)

# Define destination keys
destination_keys = list(zip(df['Destination'], df['transportation type'], df['climate'], df['activities']))

# Initialize recommender (model updates dynamically)
recommender = DestinationRecommender(destination_keys)

# Flask API setup
app = Flask(__name__)
CORS(app)

# Connect to MySQL (XAMPP)
def connect_db():
    return pymysql.connect(
        host="localhost",
        user="root",
        password="",
        database="vta_db",
        cursorclass=pymysql.cursors.DictCursor
    )

# API to suggest 5 destinations based on user input
@app.route('/suggest_destinations', methods=['POST'])
def suggest_destinations():
    try:
        data = request.get_json()
        print("Received Data:", data)

        required_fields = ['accommodation_budget', 'transportation_budget', 'transportation_type', 'climate', 'activities']
        if not all(field in data for field in required_fields):
            return jsonify({'error': 'Missing required fields'}), 400

        accommodation_budget = float(data['accommodation_budget'])
        transportation_budget = float(data['transportation_budget'])
        transportation_type = data['transportation_type']
        climate = data['climate']
        activities = data['activities']

        suggested_destinations = []
        for _ in range(5):
            suggestion = recommender.suggest_destination(accommodation_budget, transportation_budget, transportation_type, climate, activities)
            suggested_destinations.append({
                "destination": suggestion[0],
                "transportation_type": suggestion[1],
                "climate": suggestion[2],
                "activities": suggestion[3]
            })

        return jsonify({'suggested_destinations': suggested_destinations})

    except Exception as e:
        print("Error:", str(e))
        return jsonify({'error': f'Internal server error: {str(e)}'}), 500

# API to receive user feedback and dynamically update Q-table
# API to receive user feedback and store only positive feedback in the database
# API to receive user feedback and store only positive feedback in the database
@app.route('/submit_feedback', methods=['POST'])
def submit_feedback():
    try:
        feedback_data = request.get_json()
        print("Received Feedback:", feedback_data)

        if 'user_id' not in feedback_data or 'feedback' not in feedback_data:
            return jsonify({'error': 'Missing user_id or feedback data'}), 400

        user_id = feedback_data['user_id']
        positive_destinations = []

        for entry in feedback_data['feedback']:
            try:
                # Use .get() to avoid KeyErrors
                destination = (
                    entry.get('destination', 'Unknown'),  # Default if missing
                    entry.get('transportation type', 'Unknown'),  # Default if missing
                    entry.get('climate', 'Unknown'),  # Default if missing
                    entry.get('activities', 'Unknown')  # Default if missing
                )
                feedback = entry.get('feedback', '').lower()

                if feedback == "yes":
                    positive_destinations.append(destination)

            except Exception as entry_error:
                print(f"Skipping entry due to error: {entry_error}")
                continue  # Skip problematic entry but continue processing others

        # Insert only successfully processed destinations into the database
        if positive_destinations:
            try:
                conn = connect_db()
                cursor = conn.cursor()
                cursor.executemany(
                    "INSERT INTO recommendation (UserID, Destination) VALUES (%s, %s)", 
                    [(user_id, dest[0]) for dest in positive_destinations]
                )
                conn.commit()
                cursor.close()
                conn.close()
            except Exception as db_error:
                print(f"Database error: {db_error}")

        return jsonify({'message': 'Feedback received and stored!'})

    except Exception as e:
        print("Error:", str(e))
        return jsonify({'error': f'Internal server error: {str(e)}'}), 500


if __name__ == '__main__':
    app.run(debug=True)
