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

// Get dashboard statistics
$stats = [];
$query = "SELECT COUNT(*) as total FROM reservations WHERE status = 'confirmed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['confirmed_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM rooms WHERE status = 'occupied'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['occupied_rooms'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM housekeeping_tasks WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get revenue trends
$query = "SELECT DATE(created_at) as date, SUM(total_amount) as revenue 
          FROM reservations 
          WHERE status != 'cancelled' 
          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
          GROUP BY DATE(created_at) 
          ORDER BY date";
$revenue_data = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get recent AI decisions
$query = "SELECT * FROM ai_predictions 
          ORDER BY created_at DESC 
          LIMIT 10";
$ai_decisions = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Get high-risk reservations
$query = "SELECT * FROM reservations WHERE ai_cancellation_probability > 0.7 ORDER BY ai_cancellation_probability DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$high_risk_reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// AI-generated insights
$ai_insights = $ai_model->generateDashboardInsights();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-hotel me-2"></i>AI Hotel Manager
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="reservations.php"><i class="fas fa-calendar-check me-1"></i>Reservations</a>
                <a class="nav-link" href="guests.php"><i class="fas fa-users me-1"></i>Guests</a>
                <a class="nav-link" href="rooms.php"><i class="fas fa-door-open me-1"></i>Rooms</a>
                <a class="nav-link" href="housekeeping.php"><i class="fas fa-broom me-1"></i>Housekeeping</a>
                <a class="nav-link" href="ai_chatbot.php"><i class="fas fa-robot me-1"></i>AI Assistant</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['confirmed_bookings']; ?></h4>
                                <p>Confirmed Bookings</p>
                            </div>
                            <i class="fas fa-calendar-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['occupied_rooms']; ?></h4>
                                <p>Occupied Rooms</p>
                            </div>
                            <i class="fas fa-door-closed fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo $stats['pending_tasks']; ?></h4>
                                <p>Pending Tasks</p>
                            </div>
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 id="ai-predictions"><?php echo count($ai_decisions); ?></h4>
                                <p>AI Decisions Today</p>
                            </div>
                            <i class="fas fa-brain fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Revenue Trends Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Revenue Trends - Last 30 Days</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent AI Decisions -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-robot me-2"></i>Recent AI Decisions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($ai_decisions as $decision): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo $decision['prediction_type']; ?></h6>
                                        <small><?php echo date('H:i', strtotime($decision['created_at'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo substr($decision['output_data'], 0, 50) . '...'; ?></p>
                                    <small>Confidence: <?php echo ($decision['confidence_score'] * 100); ?>%</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- High Risk Reservations -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>High Cancellation Risk</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($high_risk_reservations as $reservation): ?>
                            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Booking <?php echo $reservation['booking_id']; ?></strong>
                                    <br>
                                    <small>Risk:
                                        <?php echo round($reservation['ai_cancellation_probability'] * 100); ?>%</small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger">Take Action</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- AI Insights -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>AI Strategic
                            Insights</h5>
                        </div>
                    <div class="card-body">
                        <?php foreach ($ai_insights as $insight): ?>
                            <div class="alert alert-<?php echo $insight['type']; ?>">
                                <i class="me-2 <?php echo $insight['icon']; ?>"></i>
                                <strong><?php echo $insight['title']; ?></strong>
                                <p class="mb-0"><?php echo $insight['text']; ?></p>
                                </div>
                        <?php endforeach; ?>
                        <div class="alert alert-success">
                            <i class="fas fa-tools me-2"></i>
                            <strong>Maintenance Optimization</strong>
                            <p class="mb-0">Predictive maintenance can reduce downtime by 30%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Metrics -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Real-time Performance Metrics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-occupancy">72%</h3>
                                    <small>Live Occupancy</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-revenue">$2,847</h3>
                                    <small>Today's Revenue</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-checkins">18</h3>
                                    <small>Today's Check-ins</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-satisfaction">4.2</h3>
                                    <small>Guest Rating</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-efficiency">87%</h3>
                                    <small>AI Efficiency</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="metric-card">
                                    <h3 id="live-predictions">42</h3>
                                    <small>AI Predictions</small>
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
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenue_data, 'date')); ?>,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: <?php echo json_encode(array_column($revenue_data, 'revenue')); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue ($)'
                        }
                    }
                }
            }
        });

        // Real-time metrics simulation
        function updateLiveMetrics() {
            // Simulate real-time data updates
            document.getElementById('live-occupancy').textContent =
                Math.floor(65 + Math.random() * 20) + '%';
            document.getElementById('live-revenue').textContent =
                '$' + Math.floor(2000 + Math.random() * 1000).toLocaleString();
            document.getElementById('live-checkins').textContent =
                Math.floor(15 + Math.random() * 10);
            document.getElementById('live-satisfaction').textContent =
                (3.8 + Math.random() * 0.8).toFixed(1);
            document.getElementById('live-efficiency').textContent =
                Math.floor(80 + Math.random() * 15) + '%';
            document.getElementById('live-predictions').textContent =
                Math.floor(40 + Math.random() * 10);
        }

        // Update metrics every 5 seconds
        setInterval(updateLiveMetrics, 5000);
        updateLiveMetrics(); // Initial update

        // Simulate AI prediction count updates
        setInterval(() => {
            const currentCount = parseInt(document.getElementById('ai-predictions').textContent);
            document.getElementById('ai-predictions').textContent = currentCount + 1;
        }, 30000); // Add one prediction every 30 seconds
    </script>

    <style>
        .metric-card {
            padding: 15px;
            border: 1px solid #e1e5e9;
            border-radius: 10px;
            background: #f8f9fa;
        }

        .metric-card h3 {
            margin: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .metric-card small {
            color: #6c757d;
            font-weight: 500;
        }
    </style>
</body>

</html>