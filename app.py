from flask import Flask, request, jsonify
from flask_cors import CORS  # Enable CORS for frontend compatibility
import joblib
import random
import pymysql

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

# Load trained model
try:
    recommender = joblib.load('destination_recommender4.pkl')
    print("Model loaded successfully.")
except Exception as e:
    print(f"Error loading model: {e}")
    recommender = None

app = Flask(__name__)
CORS(app)  # Enable CORS to allow cross-origin requests (frontend compatibility)

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
    if not recommender:
        return jsonify({'error': 'Model not loaded'}), 500

    try:
        data = request.get_json()
        print("Received Data:", data)  

        # Validate input
        required_fields = ['accommodation_budget', 'transportation_budget', 'transportation_type', 'climate', 'activities']
        if not all(field in data for field in required_fields):
            return jsonify({'error': 'Missing required fields'}), 400

        # Extract user input
        accommodation_budget = float(data['accommodation_budget'])
        transportation_budget = float(data['transportation_budget'])
        transportation_type = data['transportation_type']
        climate = data['climate']
        activities = data['activities']

        # Get 5 destination suggestions
        suggested_destinations = []
        for _ in range(5):
            suggestion = recommender.suggest_destination(accommodation_budget, transportation_budget, transportation_type, climate, activities)
            suggested_destinations.append({
                "destination": suggestion,
                "transportation_type": transportation_type,
                "climate": climate,
                "activities": activities
            })

        return jsonify({'suggested_destinations': suggested_destinations})

    except Exception as e:
        print("Error:", str(e))  
        return jsonify({'error': f'Internal server error: {str(e)}'}), 500

# API to receive user feedback and store positive destinations in MySQL
@app.route('/submit_feedback', methods=['POST'])
def submit_feedback():
    try:
        feedback_data = request.get_json()
        print("Received Feedback:", feedback_data)  

        if 'user_id' not in feedback_data or 'feedback' not in feedback_data:
            return jsonify({'error': 'Missing user_id or feedback data'}), 400

        user_id = feedback_data['user_id']
        positive_destinations = []

        # Process feedback
        for entry in feedback_data['feedback']:
            destination = entry['destination']
            feedback = entry['feedback']

            if feedback.lower() == "yes":
                positive_destinations.append(destination)

        # Insert into MySQL with user_id
        if positive_destinations:
            conn = connect_db()
            cursor = conn.cursor()
            cursor.executemany("INSERT INTO recommendation (UserID, Destination) VALUES (%s, %s)", [(user_id, dest) for dest in positive_destinations])
            conn.commit()
            cursor.close()
            conn.close()

        return jsonify({'message': 'Feedback received and saved!'})

    except Exception as e:
        print("Error:", str(e))
        return jsonify({'error': f'Internal server error: {str(e)}'}), 500

if __name__ == '__main__':
    app.run(debug=True)
