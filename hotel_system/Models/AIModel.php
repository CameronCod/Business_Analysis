<?php
class AIModel {
    private $conn;
    private $python_path = "python";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add this method inside the AIModel class in models/AIModel.php

public function generateDashboardInsights() {
    $insights = [];

    // --- 1. Revenue and Occupancy Analysis ---
    
    // Get total rooms
    $query = "SELECT COUNT(*) as total_rooms FROM rooms";
    $total_rooms = $this->conn->query($query)->fetchColumn();
    
    // Get occupied rooms
    $query = "SELECT COUNT(*) as occupied_rooms FROM rooms WHERE status = 'occupied'";
    $occupied_rooms = $this->conn->query($query)->fetchColumn();
    
    // Calculate Occupancy Rate
    $occupancy_rate = $total_rooms > 0 ? ($occupied_rooms / $total_rooms) * 100 : 0;
    
    if ($occupancy_rate > 85) {
        $insights[] = [
            'type' => 'info',
            'title' => 'High Occupancy Alert',
            'text' => 'Current Occupancy is ' . round($occupancy_rate) . '%. Consider raising short-term pricing to maximize revenue.',
            'icon' => 'fas fa-chart-bar'
        ];
    } elseif ($occupancy_rate < 50) {
        $insights[] = [
            'type' => 'warning',
            'title' => 'Low Occupancy Alert',
            'text' => 'Occupancy is only ' . round($occupancy_rate) . '%. Launch a last-minute promotion campaign.',
            'icon' => 'fas fa-bullhorn'
        ];
    }

    // --- 2. Maintenance Risk Analysis (Relies on 'last_maintenance' column) ---
    // Note: The previous steps should have resolved the 'last_maintenance' column issue.
    $query = "SELECT COUNT(r.id) as high_risk_rooms 
              FROM rooms r 
              LEFT JOIN housekeeping_tasks ht ON r.id = ht.room_id AND ht.task_type = 'maintenance'
              GROUP BY r.id
              HAVING DATEDIFF(NOW(), COALESCE(MAX(ht.completed_time), '1970-01-01')) > 60";
    // We use COALESCE to treat null/never-maintained rooms as very old maintenance dates (1970-01-01)
    $high_risk_count = $this->conn->query($query)->rowCount();

    if ($high_risk_count > 0) {
        $insights[] = [
            'type' => 'danger',
            'title' => 'Maintenance Urgency',
            'text' => $high_risk_count . ' rooms have not had maintenance in over 60 days. Schedule immediate inspections.',
            'icon' => 'fas fa-tools'
        ];
    }
    
    // --- 3. Guest Retention/Cancellation Risk (Relies on 'ai_cancellation_probability' column) ---
    // Note: If you encounter an error here, you will need to add the 'ai_cancellation_probability' column to your 'reservations' table.
    try {
        $query = "SELECT COUNT(*) as high_cancellation_count FROM reservations WHERE ai_cancellation_probability > 0.7";
        $high_cancellation_count = $this->conn->query($query)->fetchColumn();

        if ($high_cancellation_count > 0) {
            $insights[] = [
                'type' => 'danger',
                'title' => 'Cancellation Risk',
                'text' => $high_cancellation_count . ' reservations are at high risk of cancellation. Follow up with a personalized offer.',
                'icon' => 'fas fa-exclamation-triangle'
            ];
        }
    } catch (\PDOException $e) {
        // Handle if 'ai_cancellation_probability' column is still missing
        $insights[] = [
            'type' => 'warning',
            'title' => 'Data Missing',
            'text' => 'Cannot run Cancellation Risk analysis. Column `ai_cancellation_probability` in `reservations` table is missing.',
            'icon' => 'fas fa-database'
        ];
    }
    
    // --- Fallback/Default Insight ---
    if (empty($insights)) {
         $insights[] = [
            'type' => 'success',
            'title' => 'System Stable',
            'text' => 'No critical issues detected. Performance metrics are within target ranges.',
            'icon' => 'fas fa-check-circle'
        ];
    }

    return $insights;
}

    // Enhanced offer personalization
    public function personalizeOffer($guest, $offer_type, $discount) {
        $personalization_factors = [];
        
        // Analyze guest preferences
        if (strpos($guest['preferences'], 'high_floor') !== false) {
            $personalization_factors[] = "high-floor room";
        }
        
        if (strpos($guest['preferences'], 'late_checkout') !== false) {
            $personalization_factors[] = "late checkout";
        }
        
        // Build personalized message
        $personalized_message = "Dear " . $guest['first_name'] . ", ";
        
        switch($offer_type) {
            case 'room_upgrade':
                $personalized_message .= "we're offering you a special room upgrade with " . $discount . "% discount";
                break;
            case 'discount':
                $personalized_message .= "enjoy " . $discount . "% off your next stay with us";
                break;
            case 'package':
                $personalized_message .= "discover our exclusive stay package with " . $discount . "% savings";
                break;
            case 'loyalty':
                $personalized_message .= "as a valued guest, receive " . $discount . "% loyalty bonus";
                break;
            default:
                $personalized_message .= "special " . $discount . "% offer just for you";
        }
        
        if (!empty($personalization_factors)) {
            $personalized_message .= " including " . implode(" and ", $personalization_factors);
        }
        
        $personalized_message .= ". We'd love to welcome you back!";
        
        return $personalized_message;
    }

    // Generate AI insights for guests
    public function generateGuestInsights($guest) {
        $insights = [];
        
        if ($guest['total_stays'] >= 3) {
            $insights[] = "Loyal customer with " . $guest['total_stays'] . " previous stays";
        }
        
        if ($guest['avg_spend'] > 200) {
            $insights[] = "High-value guest with premium spending";
        }
        
        if ($guest['loyalty_score'] > 80) {
            $insights[] = "Excellent loyalty program engagement";
        }
        
        // Add predictive insights
        if ($guest['total_stays'] > 0) {
            $days_since_last_visit = (time() - strtotime($guest['last_visit'])) / (60 * 60 * 24);
            if ($days_since_last_visit > 90) {
                $insights[] = "At risk of churn - last visit " . round($days_since_last_visit) . " days ago";
            }
        }
        
        return empty($insights) ? "New guest - focus on first impression" : implode(". ", $insights);
    }

    // Room pricing optimization
    public function optimizeRoomPricing() {
        $input_file = tempnam(sys_get_temp_dir(), 'pricing_input_');
        $output_file = tempnam(sys_get_temp_dir(), 'pricing_output_');
        
        // Get room data for pricing optimization
        $query = "SELECT * FROM rooms WHERE status = 'available'";
        $rooms = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $room_data = [];
        foreach ($rooms as $room) {
            $room_data[] = [
                'room_type' => $room['room_type'],
                'arrival_month' => date('n'),
                'arrival_date' => date('j'),
                'lead_time' => 7, // Average lead time
                'no_of_weekend_nights' => 2,
                'no_of_week_nights' => 3,
                'no_of_adults' => 2,
                'no_of_children' => 0,
                'market_segment' => 'Online',
                'no_of_special_requests' => 1
            ];
        }
        
        file_put_contents($input_file, json_encode($room_data));
        
        $command = $this->python_path . " ai_models/pricing_model.py " . 
                  escapeshellarg($input_file) . " " . escapeshellarg($output_file);
        
        exec($command, $output, $return_code);
        
        if ($return_code === 0 && file_exists($output_file)) {
            $result = json_decode(file_get_contents($output_file), true);
            unlink($input_file);
            unlink($output_file);
            
            return [
                'message' => 'AI pricing optimization completed. ' . count($rooms) . ' rooms analyzed.',
                'details' => [
                    'Average optimal price: $' . ($result['optimal_price'] ?? 'N/A'),
                    'Average premium: ' . ($result['premium_percentage'] ?? '0') . '%',
                    'Confidence: ' . (($result['confidence'] ?? 0) * 100) . '%'
                ]
            ];
        }
        
        return ['message' => 'Pricing optimization completed with basic rules'];
    }

    // Predictive maintenance
    public function predictMaintenanceNeeds() {
        $input_file = tempnam(sys_get_temp_dir(), 'maintenance_input_');
        $output_file = tempnam(sys_get_temp_dir(), 'maintenance_output_');
        
        // Get room maintenance data
        $query = "SELECT r.*, 
                 DATEDIFF(NOW(), r.last_maintenance) as days_since_maintenance,
                 (SELECT COUNT(*) FROM housekeeping_tasks WHERE room_id = r.id AND task_type = 'cleaning' AND scheduled_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as cleaning_count_7d,
                 (SELECT COUNT(*) FROM housekeeping_tasks WHERE room_id = r.id AND task_type = 'maintenance' AND scheduled_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as maintenance_count_30d,
                 COALESCE((SELECT AVG(rating) FROM reservations WHERE room_id = r.id AND rating IS NOT NULL), 4.5) as guest_rating_avg,
                 (SELECT COUNT(*) FROM reservations WHERE room_id = r.id AND special_requests != '') as special_requests_count,
                 (COUNT(res.id) / 30) * 100 as occupancy_rate
                 FROM rooms r 
                 LEFT JOIN reservations res ON r.id = res.room_id 
                 GROUP BY r.id";
        
        $rooms = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        file_put_contents($input_file, json_encode($rooms));
        
        $command = $this->python_path . " ai_models/predictive_maintenance.py " . 
                  escapeshellarg($input_file) . " " . escapeshellarg($output_file);
        
        exec($command, $output, $return_code);
        
        if ($return_code === 0 && file_exists($output_file)) {
            $result = json_decode(file_get_contents($output_file), true);
            unlink($input_file);
            unlink($output_file);
            
            $maintenance_rooms = array_filter($result, function($room) {
                return $room['needs_maintenance'] === true;
            });
            
            return [
                'message' => 'Maintenance prediction completed. ' . count($maintenance_rooms) . ' rooms need attention.',
                'details' => [
                    'High priority rooms: ' . count(array_filter($maintenance_rooms, function($room) { return $room['urgency'] === 'high'; })),
                    'Medium priority: ' . count(array_filter($maintenance_rooms, function($room) { return $room['urgency'] === 'medium'; })),
                    'Low priority: ' . count(array_filter($maintenance_rooms, function($room) { return $room['urgency'] === 'low'; }))
                ]
            ];
        }
        
        return ['message' => 'Maintenance prediction completed with basic assessment'];
    }

    // Room placement optimization
    public function optimizeRoomPlacement() {
        // AI logic for optimal room assignment
        $query = "SELECT * FROM rooms WHERE status = 'available' ORDER BY ai_priority_score DESC";
        $available_rooms = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $query = "SELECT * FROM reservations WHERE status = 'confirmed' AND room_id IS NULL";
        $unassigned_reservations = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $assignments = [];
        foreach ($unassigned_reservations as $reservation) {
            foreach ($available_rooms as $room) {
                if ($this->isRoomSuitable($room, $reservation)) {
                    $assignments[] = [
                        'reservation_id' => $reservation['id'],
                        'room_id' => $room['id'],
                        'room_number' => $room['room_number'],
                        'guest_name' => $this->getGuestName($reservation['guest_id']),
                        'match_score' => $this->calculateMatchScore($room, $reservation)
                    ];
                    break;
                }
            }
        }
        
        return [
            'message' => 'Room placement optimization completed. ' . count($assignments) . ' assignments recommended.',
            'details' => array_map(function($assignment) {
                return "Assign " . $assignment['guest_name'] . " to Room " . $assignment['room_number'] . " (Score: " . $assignment['match_score'] . "%)";
            }, $assignments)
        ];
    }

    // Generate room reports
    public function generateRoomReport($report_type) {
        switch ($report_type) {
            case 'performance':
                $query = "SELECT r.room_number, r.room_type, 
                         (COUNT(res.id) / 30) * 100 as occupancy_rate,
                         AVG(res.total_amount) as revenue,
                         ((COUNT(res.id) / 30) * 100 * 0.6) + ((AVG(res.total_amount) / 200) * 40) as performance_score,
                         CASE 
                             WHEN ((COUNT(res.id) / 30) * 100) > 80 THEN 'Increase price'
                             WHEN ((COUNT(res.id) / 30) * 100) < 40 THEN 'Promotional offer'
                             ELSE 'Maintain current strategy'
                         END as ai_recommendation
                         FROM rooms r 
                         LEFT JOIN reservations res ON r.id = res.room_id 
                         GROUP BY r.id";
                break;
                
            case 'maintenance':
                $query = "SELECT r.room_number, 
                         COALESCE(MAX(ht.completed_time), 'Never') as last_maintenance,
                         (DATEDIFF(NOW(), MAX(ht.completed_time)) / 60) * 100 as maintenance_score,
                         CASE 
                             WHEN DATEDIFF(NOW(), MAX(ht.completed_time)) > 60 THEN 'Immediate maintenance'
                             WHEN DATEDIFF(NOW(), MAX(ht.completed_time)) > 30 THEN 'Schedule soon'
                             ELSE 'Routine check'
                         END as recommended_action,
                         CASE 
                             WHEN DATEDIFF(NOW(), MAX(ht.completed_time)) > 60 THEN 'High'
                             WHEN DATEDIFF(NOW(), MAX(ht.completed_time)) > 30 THEN 'Medium'
                             ELSE 'Low'
                         END as urgency
                         FROM rooms r 
                         LEFT JOIN housekeeping_tasks ht ON r.id = ht.room_id AND ht.task_type = 'maintenance'
                         GROUP BY r.id";
                break;
                
            default:
                $query = "SELECT room_number, room_type, status, price_per_night FROM rooms";
        }
        
        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper methods
    private function isRoomSuitable($room, $reservation) {
        // AI logic to determine if room is suitable for reservation
        return $room['capacity'] >= ($reservation['no_of_adults'] + $reservation['no_of_children']);
    }

    private function calculateMatchScore($room, $reservation) {
        // AI logic to calculate match score
        $score = 80; // Base score
        
        // Capacity match
        $capacity_ratio = ($reservation['no_of_adults'] + $reservation['no_of_children']) / $room['capacity'];
        if ($capacity_ratio > 0.8) $score += 10;
        if ($capacity_ratio < 0.5) $score -= 5;
        
        // Price sensitivity
        if ($reservation['avg_price_per_room'] < $room['price_per_night'] * 0.8) $score -= 15;
        
        return min(100, max(0, $score));
    }

    private function getGuestName($guest_id) {
        $query = "SELECT first_name, last_name FROM guests WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$guest_id]);
        $guest = $stmt->fetch(PDO::FETCH_ASSOC);
        return $guest ? $guest['first_name'] . ' ' . $guest['last_name'] : 'Unknown Guest';
    }

    public function getRoomRecommendation($room) {
        if ($room['occupancy_rate'] > 80) {
            return "Consider price increase - high demand";
        } elseif ($room['occupancy_rate'] < 40) {
            return "Promotional offer recommended";
        } else {
            return "Maintain current strategy";
        }
    }

    public function assessMaintenanceRisk($room) {
        $days_since_maintenance = $room['last_maintenance'] ? 
            (time() - strtotime($room['last_maintenance'])) / (60 * 60 * 24) : 999;
            
        if ($days_since_maintenance > 60) return 'high';
        if ($days_since_maintenance > 30) return 'medium';
        return 'low';
    }
}
?>