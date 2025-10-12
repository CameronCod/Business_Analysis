<?php
session_start();
require_once "config/database.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($_POST && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Log login activity
        $log_query = "INSERT INTO user_activity (user_id, activity_type) VALUES (?, 'login')";
        $db->prepare($log_query)->execute([$user['id']]);
        
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .ai-features {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card login-card">
                        <div class="row g-0">
                            <div class="col-md-6">
                                <div class="card-body p-5">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-hotel fa-3x text-primary mb-3"></i>
                                        <h2>AI Hotel Manager</h2>
                                        <p class="text-muted">Staff Login</p>
                                    </div>
                                    
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                <input type="text" name="username" class="form-control" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="password" class="form-control" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="remember">
                                            <label class="form-check-label" for="remember">Remember me</label>
                                        </div>
                                        
                                        <button type="submit" name="login" class="btn btn-primary w-100 py-2">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                    </form>
                                    
                                    <div class="text-center mt-3">
                                        <a href="register.php" class="text-decoration-none">Create new account</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="ai-features h-100 d-flex align-items-center">
                                    <div>
                                        <h4 class="mb-4">AI-Powered Features</h4>
                                        <div class="mb-3">
                                            <i class="fas fa-brain me-2"></i>
                                            <span>Smart Cancellation Predictions</span>
                                        </div>
                                        <div class="mb-3">
                                            <i class="fas fa-robot me-2"></i>
                                            <span>Automated Housekeeping</span>
                                        </div>
                                        <div class="mb-3">
                                            <i class="fas fa-chart-line me-2"></i>
                                            <span>Real-time Analytics</span>
                                        </div>
                                        <div class="mb-3">
                                            <i class="fas fa-magic me-2"></i>
                                            <span>AI Guest Recommendations</span>
                                        </div>
                                        <div class="mb-3">
                                            <i class="fas fa-bolt me-2"></i>
                                            <span>Optimized Room Pricing</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>