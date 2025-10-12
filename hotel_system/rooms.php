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

// Handle room updates
if ($_POST && isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];
    $status = $_POST['status'];
    $ai_priority = $_POST['ai_priority'];
    
    $query = "UPDATE rooms SET status = ?, ai_priority_score = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$status, $ai_priority, $room_id])) {
        $_SESSION['success'] = "Room updated successfully!";
    }
}

// Handle AI Optimization
if ($_POST && isset($_POST['run_ai_optimization'])) {
    $optimization_type = $_POST['optimization_type'];
    
    if ($optimization_type === 'pricing') {
        $result = $ai_model->optimizeRoomPricing();
        $_SESSION['ai_result'] = $result;
    } elseif ($optimization_type === 'maintenance') {
        $result = $ai_model->predictMaintenanceNeeds();
        $_SESSION['ai_result'] = $result;
    } elseif ($optimization_type === 'placement') {
        $result = $ai_model->optimizeRoomPlacement();
        $_SESSION['ai_result'] = $result;
    }
}

// Handle Export Report
if ($_POST && isset($_POST['export_report'])) {
    $report_type = $_POST['report_type'];
    $report_data = $ai_model->generateRoomReport($report_type);
    
    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="room_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if ($report_type === 'performance') {
        fputcsv($output, ['Room Number', 'Room Type', 'Occupancy Rate', 'Revenue', 'Performance Score', 'AI Recommendation']);
        foreach ($report_data as $row) {
            fputcsv($output, $row);
        }
    } elseif ($report_type === 'maintenance') {
        fputcsv($output, ['Room Number', 'Last Maintenance', 'Maintenance Score', 'Recommended Action', 'Urgency']);
        foreach ($report_data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

// Get all rooms with AI insights
$query = "SELECT r.*, 
          COUNT(res.id) as total_bookings,
          AVG(CASE WHEN res.status != 'cancelled' THEN res.total_amount ELSE 0 END) as avg_revenue,
          MAX(res.check_out) as last_occupied
          FROM rooms r 
          LEFT JOIN reservations res ON r.id = res.room_id 
          GROUP BY r.id 
          ORDER BY r.room_number";
$stmt = $db->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate AI performance metrics
foreach ($rooms as &$room) {
    $room['occupancy_rate'] = min(100, ($room['total_bookings'] / 30) * 100);
    $room['performance_score'] = ($room['occupancy_rate'] * 0.6) + (($room['avg_revenue'] / 200) * 40);
    $room['ai_recommendation'] = $ai_model->getRoomRecommendation($room);
    $room['maintenance_risk'] = $ai_model->assessMaintenanceRisk($room);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <!-- AI Optimization Results -->
        <?php if (isset($_SESSION['ai_result'])): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <h5><i class="fas fa-robot me-2"></i>AI Optimization Complete</h5>
            <p class="mb-0"><?php echo $_SESSION['ai_result']['message']; ?></p>
            <?php if (isset($_SESSION['ai_result']['details'])): ?>
                <ul class="mt-2">
                    <?php foreach ($_SESSION['ai_result']['details'] as $detail): ?>
                        <li><?php echo $detail; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['ai_result']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <!-- Quick Actions Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>AI Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="optimization_type" value="pricing">
                                    <button type="submit" name="run_ai_optimization" class="btn btn-success w-100 h-100 py-3">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i><br>
                                        <strong>Optimize Pricing</strong><br>
                                        <small>AI-driven dynamic pricing</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="optimization_type" value="maintenance">
                                    <button type="submit" name="run_ai_optimization" class="btn btn-warning w-100 h-100 py-3">
                                        <i class="fas fa-tools fa-2x mb-2"></i><br>
                                        <strong>Predict Maintenance</strong><br>
                                        <small>AI maintenance forecasting</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="optimization_type" value="placement">
                                    <button type="submit" name="run_ai_optimization" class="btn btn-info w-100 h-100 py-3">
                                        <i class="fas fa-project-diagram fa-2x mb-2"></i><br>
                                        <strong>Room Placement</strong><br>
                                        <small>AI guest assignment</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3">
                                <div class="dropdown h-100">
                                    <button class="btn btn-secondary w-100 h-100 py-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-file-export fa-2x mb-2"></i><br>
                                        <strong>Export Reports</strong><br>
                                        <small>AI-generated insights</small>
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li>
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="performance">
                                                <button type="submit" name="export_report" class="dropdown-item">
                                                    <i class="fas fa-chart-bar me-2"></i>Performance Report
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="maintenance">
                                                <button type="submit" name="export_report" class="dropdown-item">
                                                    <i class="fas fa-tools me-2"></i>Maintenance Report
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="revenue">
                                                <button type="submit" name="export_report" class="dropdown-item">
                                                    <i class="fas fa-dollar-sign me-2"></i>Revenue Report
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Analytics -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>AI Room Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-primary"><?php echo count($rooms); ?></h4>
                                    <small class="text-muted">Total Rooms</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-success">
                                        <?php 
                                        $available = array_filter($rooms, function($r) { return $r['status'] == 'available'; });
                                        echo count($available);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Available</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-warning">
                                        <?php 
                                        $occupied = array_filter($rooms, function($r) { return $r['status'] == 'occupied'; });
                                        echo count($occupied);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Occupied</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-danger">
                                        <?php 
                                        $maintenance = array_filter($rooms, function($r) { return $r['status'] == 'maintenance'; });
                                        echo count($maintenance);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Maintenance</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-secondary">
                                        <?php 
                                        $cleaning = array_filter($rooms, function($r) { return $r['status'] == 'cleaning'; });
                                        echo count($cleaning);
                                        ?>
                                    </h4>
                                    <small class="text-muted">Cleaning</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3">
                                    <h4 class="text-dark" id="total-revenue">
                                        $<?php echo array_sum(array_column($rooms, 'avg_revenue')); ?>
                                    </h4>
                                    <small class="text-muted">Daily Revenue</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <!-- Rooms Grid -->
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-door-open me-2"></i>AI-Optimized Room Management</h5>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-light" onclick="filterRooms('all')">All</button>
                            <button class="btn btn-sm btn-light" onclick="filterRooms('available')">Available</button>
                            <button class="btn btn-sm btn-light" onclick="filterRooms('occupied')">Occupied</button>
                            <button class="btn btn-sm btn-light" onclick="filterRooms('maintenance')">Maintenance</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="rooms-grid">
                            <?php foreach ($rooms as $room): ?>
                            <div class="col-md-3 mb-3 room-card" data-status="<?php echo $room['status']; ?>">
                                <div class="card h-100 room-card-<?php echo $room['status']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0"><?php echo $room['room_number']; ?></h5>
                                            <div>
                                                <span class="badge bg-<?php 
                                                    echo $room['status'] == 'available' ? 'success' : 
                                                         ($room['status'] == 'occupied' ? 'warning' : 
                                                         ($room['status'] == 'maintenance' ? 'danger' : 'secondary')); 
                                                ?>">
                                                    <?php echo ucfirst($room['status']); ?>
                                                </span>
                                                <?php if ($room['maintenance_risk'] == 'high'): ?>
                                                    <span class="badge bg-danger ms-1" title="High maintenance risk"><i class="fas fa-exclamation-triangle"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <p class="card-text mb-1">
                                            <small class="text-muted"><?php echo $room['room_type']; ?></small>
                                        </p>
                                        <p class="card-text mb-1">
                                            <strong>$<?php echo $room['price_per_night']; ?></strong>/night
                                        </p>
                                        
                                        <!-- AI Recommendations -->
                                        <div class="ai-recommendation mb-2">
                                            <small class="text-info">
                                                <i class="fas fa-robot me-1"></i>
                                                <?php echo $room['ai_recommendation']; ?>
                                            </small>
                                        </div>
                                        
                                        <div class="room-stats mt-3">
                                            <div class="mb-2">
                                                <small>Occupancy Rate</small>
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-info" 
                                                         style="width: <?php echo $room['occupancy_rate']; ?>%"></div>
                                                </div>
                                                <small><?php echo round($room['occupancy_rate']); ?>%</small>
                                            </div>
                                            
                                            <div class="mb-2">
                                                <small>Performance Score</small>
                                                <div class="progress" style="height: 5px;">
                                                    <div class="progress-bar bg-<?php 
                                                        echo $room['performance_score'] > 70 ? 'success' : 
                                                             ($room['performance_score'] > 40 ? 'warning' : 'danger'); 
                                                    ?>" style="width: <?php echo $room['performance_score']; ?>%"></div>
                                                </div>
                                                <small><?php echo round($room['performance_score']); ?>%</small>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-bed me-1"></i><?php echo $room['capacity']; ?> guests
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <button class="btn btn-sm btn-outline-primary w-100" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editRoomModal"
                                                data-room-id="<?php echo $room['id']; ?>"
                                                data-room-number="<?php echo $room['room_number']; ?>"
                                                data-current-status="<?php echo $room['status']; ?>"
                                                data-ai-priority="<?php echo $room['ai_priority_score']; ?>">
                                            <i class="fas fa-edit me-1"></i>Manage
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- AI Room Optimization Panel -->
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-robot me-2"></i>AI Optimization Panel</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb me-2"></i>Pricing Suggestions</h6>
                            <p class="mb-2">AI recommends dynamic pricing adjustments:</p>
                            <ul class="mb-0">
                                <li>Increase Room Type 4 by 15% on weekends</li>
                                <li>Reduce Room Type 1 by 10% on weekdays</li>
                                <li>Bundle offers for Room Type 6</li>
                            </ul>
                        </div>
                        
                        <div class="alert alert-success">
                            <h6><i class="fas fa-magic me-2"></i>Maintenance Predictions</h6>
                            <p class="mb-0">3 rooms predicted for maintenance in next 7 days</p>
                        </div>
                        
                        <div class="alert alert-primary">
                            <h6><i class="fas fa-chart-line me-2"></i>Occupancy Forecast</h6>
                            <p class="mb-2">Next 30 days prediction:</p>
                            <div class="progress mb-1" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: 85%"></div>
                            </div>
                            <small>85% expected occupancy</small>
                        </div>

                        <!-- Real-time AI Decisions -->
                        <div class="mt-4">
                            <h6><i class="fas fa-bolt me-2"></i>Recent AI Decisions</h6>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <small class="text-success">✓ Room 101 price increased by 12%</small>
                                </div>
                                <div class="list-group-item">
                                    <small class="text-warning">⚠ Room 205 maintenance scheduled</small>
                                </div>
                                <div class="list-group-item">
                                    <small class="text-info">ℹ Room 312 assigned to VIP guest</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                            <i class="fas fa-plus me-2"></i>Add New Room
                        </button>
                        <button class="btn btn-info w-100 mb-2" onclick="runQuickOptimization()">
                            <i class="fas fa-sync me-2"></i>Run Quick AI Optimization
                        </button>
                        <button class="btn btn-warning w-100" onclick="generateAISummary()">
                            <i class="fas fa-file-alt me-2"></i>Generate AI Summary
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Room Modal -->
    <div class="modal fade" id="editRoomModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Room <span id="modal-room-number"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="room_id" id="edit-room-id">
                        
                        <div class="mb-3">
                            <label class="form-label">Room Status</label>
                            <select name="status" class="form-select" id="edit-room-status">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="cleaning">Cleaning</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">AI Priority Score</label>
                            <input type="range" name="ai_priority" class="form-range" 
                                   min="0" max="1" step="0.1" id="edit-ai-priority">
                            <div class="d-flex justify-content-between">
                                <small>Low Priority</small>
                                <small>High Priority</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-robot me-2"></i>
                            <strong>AI Insight:</strong> 
                            <span id="room-ai-insight">Adjust priority based on room performance and demand</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_room" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Room filtering
        function filterRooms(status) {
            const rooms = document.querySelectorAll('.room-card');
            rooms.forEach(room => {
                if (status === 'all' || room.getAttribute('data-status') === status) {
                    room.style.display = 'block';
                } else {
                    room.style.display = 'none';
                }
            });
        }

        // Modal handling
        const editRoomModal = document.getElementById('editRoomModal');
        editRoomModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const roomId = button.getAttribute('data-room-id');
            const roomNumber = button.getAttribute('data-room-number');
            const currentStatus = button.getAttribute('data-current-status');
            const aiPriority = button.getAttribute('data-ai-priority');
            
            document.getElementById('modal-room-number').textContent = roomNumber;
            document.getElementById('edit-room-id').value = roomId;
            document.getElementById('edit-room-status').value = currentStatus;
            document.getElementById('edit-ai-priority').value = aiPriority;
            
            // Update AI insight based on room
            let insight = "Room " + roomNumber + " - ";
            if (currentStatus === 'available') {
                insight += "Consider promotional pricing to increase occupancy.";
            } else if (currentStatus === 'occupied') {
                insight += "High guest satisfaction reported. Maintain current standards.";
            } else {
                insight += "Optimize turnaround time to maximize revenue.";
            }
            document.getElementById('room-ai-insight').textContent = insight;
        });

        // Quick optimization
        function runQuickOptimization() {
            alert('Running AI optimization on all rooms... This may take a few moments.');
            // In full implementation, this would call the AI optimization API
        }

        // Generate AI summary
        function generateAISummary() {
            const summary = `
AI Room Management Summary - ${new Date().toLocaleDateString()}

• Total Rooms: <?php echo count($rooms); ?>
• Available: <?php echo count(array_filter($rooms, function($r) { return $r['status'] == 'available'; })); ?>
• Occupied: <?php echo count(array_filter($rooms, function($r) { return $r['status'] == 'occupied'; })); ?>
• Under Maintenance: <?php echo count(array_filter($rooms, function($r) { return $r['status'] == 'maintenance'; })); ?>

AI Recommendations:
1. Increase pricing for premium rooms during peak season
2. Schedule maintenance for high-risk rooms
3. Optimize room assignments based on guest preferences
4. Implement dynamic pricing for weekend stays

Expected Revenue Impact: +15-20%
            `;
            
            alert(summary);
        }

        // Simulate revenue updates
        setInterval(() => {
            const currentRevenue = parseFloat(document.getElementById('total-revenue').textContent.replace('$', ''));
            const newRevenue = currentRevenue + (Math.random() * 10 - 5);
            document.getElementById('total-revenue').textContent = '$' + Math.max(0, newRevenue).toFixed(2);
        }, 5000);
    </script>
    
    <style>
        .room-card-available { border-left: 4px solid #28a745; }
        .room-card-occupied { border-left: 4px solid #ffc107; }
        .room-card-maintenance { border-left: 4px solid #dc3545; }
        .room-card-cleaning { border-left: 4px solid #6c757d; }
        
        .room-card:hover {
            transform: translateY(-2px);
            transition: transform 0.2s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .ai-recommendation {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 5px;
            border-left: 3px solid #17a2b8;
        }
    </style>
</body>
</html>