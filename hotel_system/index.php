<?php
session_start();
require_once "config/database.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Hotel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .ai-badge {
            background: linear-gradient(45deg, #FF6B6B, #4ECDC4);
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-hotel me-2"></i>AI Hotel Manager
            </a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Intelligent Hotel Management</h1>
            <p class="lead mb-4">AI-powered system for seamless hotel operations, smart reservations, and automated housekeeping</p>
            <div class="row mt-5">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <i class="fas fa-brain fa-3x text-primary mb-3"></i>
                            <h5>AI Predictions</h5>
                            <p>Smart cancellation predictions and occupancy forecasting</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <i class="fas fa-robot fa-3x text-success mb-3"></i>
                            <h5>Automated Tasks</h5>
                            <p>AI-optimized housekeeping and maintenance scheduling</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card h-100">
                        <div class="card-body">
                            <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                            <h5>Real-time Analytics</h5>
                            <p>Live dashboards with predictive insights</p>
                        </div>
                    </div>
                </div>
            </div>
            <a href="login.php" class="btn btn-light btn-lg mt-4">Staff Login</a>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>