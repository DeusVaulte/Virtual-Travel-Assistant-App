from flask import Flask, request, jsonify
import joblib
import random

# Redefine the DestinationRecommender class
class DestinationRecommender:
    def __init__(self, destinations, learning_rate=0.1, discount_factor=0.9, exploration_rate=0.2):
        self.destinations = destinations
        self.q_table = {dest: 0 for dest in destinations}
        self.lr = learning_rate
        self.gamma = discount_factor
        self.epsilon = exploration_rate

    def suggest_destination(self, accommodation_budget, transportation_budget, transportation_type):
        if random.random() < self.epsilon:
            return random.choice(self.destinations)  # Exploration
        else:
            return max(self.q_table, key=self.q_table.get)  # Exploitation

    def update_q_table(self, destination, reward):
        current_q = self.q_table[destination]
        self.q_table[destination] = current_q + self.lr * (reward + self.gamma * max(self.q_table.values()) - current_q)


app = Flask(__name__)

# Load the trained model
recommender = joblib.load('destination_recommender3.pkl')

@app.route('/suggest_destination', methods=['POST'])
def suggest_destination():
    try:
        data = request.get_json()
        print("Received Data:", data)  # Debugging line

        # Validate required fields
        required_fields = ['accommodation_budget', 'transportation_budget', 'transportation_type']
        if not data or not all(field in data for field in required_fields):
            return jsonify({'error': 'Invalid input. Required fields missing.'}), 400

        # Extract inputs from request
        accommodation_budget = float(data['accommodation_budget'])
        transportation_budget = float(data['transportation_budget'])
        transportation_type = data['transportation_type']

        # Get suggestion
        suggestion = recommender.suggest_destination(accommodation_budget, transportation_budget, transportation_type)

        return jsonify({'suggested_destination': suggestion})

    except Exception as e:
        print("Error:", str(e))  # Log errors
        return jsonify({'error': 'Internal server error'}), 500

if __name__ == '__main__':
    app.run(debug=True)
