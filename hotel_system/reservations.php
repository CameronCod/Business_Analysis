<?php
session_start();
require_once "config/database.php";
require_once "models/AIModel.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$ai_model = new AIModel($db);

// Handle new reservation
if ($_POST && isset($_POST['create_reservation'])) {
    $guest_id = $_POST['guest_id'];
    $room_id = $_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $adults = $_POST['no_of_adults'];
    $children = $_POST['no_of_children'];
    $special_requests = $_POST['special_requests'];
    
    // Generate booking ID
    $booking_id = 'INN' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    
    // Calculate total amount
    $room_query = "SELECT price_per_night FROM rooms WHERE id = ?";
    $stmt = $db->prepare($room_query);
    $stmt->execute([$room_id]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $nights = $check_out_date->diff($check_in_date)->days;
    $total_amount = $room['price_per_night'] * $nights;
    
    // AI Cancellation Prediction
    $reservation_data = [
        'lead_time' => (new DateTime())->diff(new DateTime($check_in))->days,
        'no_of_adults' => $adults,
        'no_of_children' => $children,
        'no_of_weekend_nights' => 0, // Would calculate based on dates
        'no_of_week_nights' => $nights,
        'required_car_parking_space' => 0,
        'avg_price_per_room' => $room['price_per_night'],
        'no_of_special_requests' => substr_count($special_requests, ',') + 1,
        'arrival_month' => date('m', strtotime($check_in))
    ];
    
    $ai_prediction = $ai_model->predictCancellation($reservation_data);
    $cancellation_probability = $ai_prediction['probability'];
    
    // Insert reservation
    $query = "INSERT INTO reservations (booking_id, guest_id, room_id, check_in, check_out, 
              no_of_adults, no_of_children, total_amount, special_requests, ai_cancellation_probability) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    if ($stmt->execute([$booking_id, $guest_id, $room_id, $check_in, $check_out, 
                       $adults, $children, $total_amount, $special_requests, $cancellation_probability])) {
        
        // Update room status
        $update_room = "UPDATE rooms SET status = 'occupied' WHERE id = ?";
        $db->prepare($update_room)->execute([$room_id]);
        
        $_SESSION['success'] = "Reservation created successfully! AI Cancellation Risk: " . 
                             round($cancellation_probability * 100) . "%";
    }
}

// Get all reservations with AI insights
$query = "SELECT r.*, g.first_name, g.last_name, rm.room_number, rm.room_type 
          FROM reservations r 
          JOIN guests g ON r.guest_id = g.id 
          JOIN rooms rm ON r.room_id = rm.id 
          ORDER BY r.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available rooms
$room_query = "SELECT * FROM rooms WHERE status = 'available'";
$rooms = $db->query($room_query)->fetchAll(PDO::FETCH_ASSOC);

// Get guests for dropdown
$guests = $db->query("SELECT id, first_name, last_name FROM guests")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4">
                <!-- New Reservation Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>New Reservation</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Guest</label>
                                <select name="guest_id" class="form-select" required>
                                    <option value="">Select Guest</option>
                                    <?php foreach ($guests as $guest): ?>
                                        <option value="<?php echo $guest['id']; ?>">
                                            <?php echo $guest['first_name'] . ' ' . $guest['last_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Room</label>
                                <select name="room_id" class="form-select" required>
                                    <option value="">Select Room</option>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?php echo $room['id']; ?>" data-price="<?php echo $room['price_per_night']; ?>">
                                            <?php echo $room['room_number'] . ' - ' . $room['room_type'] . ' ($' . $room['price_per_night'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Check-in</label>
                                        <input type="date" name="check_in" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Check-out</label>
                                        <input type="date" name="check_out" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Adults</label>
                                        <input type="number" name="no_of_adults" class="form-control" value="1" min="1" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Children</label>
                                        <input type="number" name="no_of_children" class="form-control" value="0" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Special Requests</label>
                                <textarea name="special_requests" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-robot me-2"></i>
                                    <strong>AI Insight:</strong> 
                                    <span id="ai-insight">Select a room to see AI recommendations</span>
                                </div>
                            </div>
                            
                            <button type="submit" name="create_reservation" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Create Reservation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Reservations List -->
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Reservations</h5>
                        <span class="badge bg-light text-dark"><?php echo count($reservations); ?> total</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Check-in/out</th>
                                        <th>Amount</th>
                                        <th>AI Risk</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $reservation['booking_id']; ?></strong>
                                            <?php if ($reservation['ai_cancellation_probability'] > 0.7): ?>
                                                <i class="fas fa-exclamation-triangle text-danger ms-1" title="High cancellation risk"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $reservation['first_name'] . ' ' . $reservation['last_name']; ?></td>
                                        <td><?php echo $reservation['room_number'] . ' (' . $reservation['room_type'] . ')'; ?></td>
                                        <td>
                                            <small><?php echo date('M j', strtotime($reservation['check_in'])); ?> - 
                                                  <?php echo date('M j', strtotime($reservation['check_out'])); ?></small>
                                        </td>
                                        <td>$<?php echo $reservation['total_amount']; ?></td>
                                        <td>
                                            <?php 
                                            $risk = $reservation['ai_cancellation_probability'];
                                            $color = $risk > 0.7 ? 'danger' : ($risk > 0.4 ? 'warning' : 'success');
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo round($risk * 100); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $reservation['status'] == 'confirmed' ? 'success' : 
                                                     ($reservation['status'] == 'checked_in' ? 'primary' : 
                                                     ($reservation['status'] == 'checked_out' ? 'secondary' : 'danger')); 
                                            ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($reservation['status'] == 'confirmed'): ?>
                                                    <button class="btn btn-sm btn-outline-success"
                                                            data-bs-toggle="tooltip" title="Check-in">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- AI Analytics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reservation Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-primary" id="avg-occupancy">72%</h4>
                                    <small class="text-muted">Avg Occupancy</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-success" id="completion-rate">88%</h4>
                                    <small class="text-muted">Completion Rate</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-warning" id="avg-stay">2.3</h4>
                                    <small class="text-muted">Avg Stay (nights)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-danger" id="cancellation-rate">12%</h4>
                                    <small class="text-muted">Cancellation Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update AI insights when room is selected
        document.querySelector('select[name="room_id"]').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            const roomType = selectedOption.textContent.split(' - ')[1];
            
            let insight = "Good choice! ";
            if (roomType.includes('Room_Type 4') || roomType.includes('Room_Type 6')) {
                insight += "Premium rooms have 25% higher guest satisfaction. Consider upselling amenities.";
            } else if (price > 150) {
                insight += "This room type shows 15% higher repeat booking rate.";
            } else {
                insight += "Popular choice for budget-conscious travelers.";
            }
            
            document.getElementById('ai-insight').textContent = insight;
        });
        
        // Simulate real-time analytics updates
        setInterval(() => {
            document.getElementById('avg-occupancy').textContent = 
                Math.floor(65 + Math.random() * 15) + '%';
            document.getElementById('completion-rate').textContent = 
                Math.floor(85 + Math.random() * 10) + '%';
        }, 10000);
    </script>
</body>
</html>