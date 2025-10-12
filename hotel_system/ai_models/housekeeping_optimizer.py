import json
import sys
import numpy as np
from datetime import datetime

class HousekeepingOptimizer:
    def __init__(self):
        self.priority_weights = {
            'urgent': 4,
            'high': 3,
            'medium': 2,
            'low': 1
        }
    
    def optimize_schedule(self, tasks):
        # Calculate scores for each task
        for task in tasks:
            task['score'] = self.calculate_task_score(task)
        
        # Sort by score (descending)
        optimized_tasks = sorted(tasks, key=lambda x: x['score'], reverse=True)
        
        # Assign sequence
        for i, task in enumerate(optimized_tasks):
            task['ai_optimized_sequence'] = i + 1
        
        return optimized_tasks
    
    def calculate_task_score(self, task):
        base_score = self.priority_weights.get(task.get('priority', 'medium'), 2)
        
        # Adjust based on room status
        if task.get('room_status') == 'checked_out':
            base_score *= 1.5
        
        # Adjust based on time constraints
        scheduled_time = task.get('scheduled_time')
        if scheduled_time:
            time_diff = (datetime.fromisoformat(scheduled_time) - datetime.now()).total_seconds() / 3600
            if time_diff < 2:  # Less than 2 hours
                base_score *= 1.3
        
        return base_score

def main():
    if len(sys.argv) != 3:
        print(json.dumps({"error": "Invalid arguments"}))
        return
    
    input_file = sys.argv[1]
    output_file = sys.argv[2]
    
    try:
        with open(input_file, 'r') as f:
            tasks = json.load(f)
        
        optimizer = HousekeepingOptimizer()
        optimized_tasks = optimizer.optimize_schedule(tasks)
        
        with open(output_file, 'w') as f:
            json.dump(optimized_tasks, f)
            
    except Exception as e:
        with open(output_file, 'w') as f:
            json.dump([], f)

if __name__ == "__main__":
    main()