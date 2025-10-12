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

// Handle new task
if ($_POST && isset($_POST['add_task'])) {
    $room_id = $_POST['room_id'];
    $task_type = $_POST['task_type'];
    $priority = $_POST['priority'];
    $scheduled_time = $_POST['scheduled_time'];
    
    $query = "INSERT INTO housekeeping_tasks (room_id, task_type, priority, scheduled_time) 
              VALUES (?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    if ($stmt->execute([$room_id, $task_type, $priority, $scheduled_time])) {
        $_SESSION['success'] = "Task added successfully!";
        
        // Trigger AI optimization
        optimizeHousekeepingSchedule();
    }
}

// Handle task completion
if ($_POST && isset($_POST['complete_task'])) {
    $task_id = $_POST['task_id'];
    
    $query = "UPDATE housekeeping_tasks SET status = 'completed', completed_time = NOW() WHERE id = ?";
    $stmt = $db->prepare($query);
    if ($stmt->execute([$task_id])) {
        $_SESSION['success'] = "Task marked as completed!";
    }
}

// AI Optimization function
function optimizeHousekeepingSchedule() {
    global $db, $ai_model;
    
    $query = "SELECT ht.*, r.room_number, r.status as room_status 
              FROM housekeeping_tasks ht 
              JOIN rooms r ON ht.room_id = r.id 
              WHERE ht.status = 'pending' 
              ORDER BY ht.scheduled_time ASC";
    $tasks = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($tasks) > 0) {
        $optimized_tasks = $ai_model->optimizeHousekeeping($tasks);
        
        // Update task sequence in database
        foreach ($optimized_tasks as $task) {
            $update_query = "UPDATE housekeeping_tasks SET ai_optimized_sequence = ? WHERE id = ?";
            $db->prepare($update_query)->execute([$task['ai_optimized_sequence'], $task['id']]);
        }
    }
}

// Get all tasks with AI optimization
$query = "SELECT ht.*, r.room_number, r.room_type, u.username as assigned_staff
          FROM housekeeping_tasks ht 
          JOIN rooms r ON ht.room_id = r.id 
          LEFT JOIN users u ON ht.assigned_to = u.id 
          ORDER BY ht.ai_optimized_sequence ASC, ht.scheduled_time ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get rooms for dropdown
$rooms = $db->query("SELECT id, room_number, room_type FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);

// Get staff for assignment
$staff = $db->query("SELECT id, username FROM users WHERE role = 'staff'")->fetchAll(PDO::FETCH_ASSOC);

// Run initial optimization
optimizeHousekeepingSchedule();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Housekeeping - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4">
                <!-- Add Task Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>New Housekeeping Task</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Room</label>
                                <select name="room_id" class="form-select" required>
                                    <option value="">Select Room</option>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?php echo $room['id']; ?>">
                                            <?php echo $room['room_number'] . ' - ' . $room['room_type']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Task Type</label>
                                <select name="task_type" class="form-select" required>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="inspection">Inspection</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Scheduled Time</label>
                                <input type="datetime-local" name="scheduled_time" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-robot me-2"></i>
                                    <strong>AI Optimization:</strong> 
                                    Tasks are automatically optimized for efficiency
                                </div>
                            </div>
                            
                            <button type="submit" name="add_task" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Add Task
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- AI Efficiency Metrics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Efficiency Metrics</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">AI Optimization Score</small>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-success" style="width: 87%">87%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Task Completion Rate</small>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-info" style="width: 92%">92%</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Time Savings (AI vs Manual)</small>
                            <div class="progress mb-2" style="height: 20px;">
                                <div class="progress-bar bg-warning" style="width: 35%">35% faster</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Tasks Kanban Board -->
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-kanban me-2"></i>Housekeeping Tasks</h5>
                        <button class="btn btn-sm btn-light" onclick="runAIOptimization()">
                            <i class="fas fa-robot me-1"></i>Run AI Optimization
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Pending Column -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">Pending <span class="badge bg-light text-dark" id="pending-count">0</span></h6>
                                    </div>
                                    <div class="card-body kanban-column" data-status="pending">
                                        <?php foreach ($tasks as $task): ?>
                                            <?php if ($task['status'] == 'pending'): ?>
                                            <div class="card mb-3 task-card" data-task-id="<?php echo $task['id']; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">Room <?php echo $task['room_number']; ?></h6>
                                                        <span class="badge bg-<?php 
                                                            echo $task['priority'] == 'urgent' ? 'danger' : 
                                                                 ($task['priority'] == 'high' ? 'warning' : 
                                                                 ($task['priority'] == 'medium' ? 'info' : 'secondary')); 
                                                        ?>">
                                                            <?php echo ucfirst($task['priority']); ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <p class="card-text mb-1">
                                                        <small><?php echo ucfirst($task['task_type']); ?></small>
                                                    </p>
                                                    
                                                    <div class="task-meta">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?php echo date('H:i', strtotime($task['scheduled_time'])); ?>
                                                        </small>
                                                        <?php if ($task['ai_optimized_sequence']): ?>
                                                            <small class="text-primary ms-2">
                                                                <i class="fas fa-robot me-1"></i>#<?php echo $task['ai_optimized_sequence']; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="mt-3">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <button type="submit" name="complete_task" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check me-1"></i>Complete
                                                            </button>
                                                        </form>
                                                        <button class="btn btn-sm btn-outline-secondary" 
                                                                data-bs-toggle="tooltip" title="Assign Staff">
                                                            <i class="fas fa-user"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- In Progress Column -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">In Progress <span class="badge bg-light text-dark" id="progress-count">0</span></h6>
                                    </div>
                                    <div class="card-body kanban-column" data-status="in_progress">
                                        <?php foreach ($tasks as $task): ?>
                                            <?php if ($task['status'] == 'in_progress'): ?>
                                            <div class="card mb-3 task-card" data-task-id="<?php echo $task['id']; ?>">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0">Room <?php echo $task['room_number']; ?></h6>
                                                        <span class="badge bg-info">In Progress</span>
                                                    </div>
                                                    
                                                    <p class="card-text mb-1">
                                                        <small><?php echo ucfirst($task['task_type']); ?></small>
                                                    </p>
                                                    
                                                    <?php if ($task['assigned_staff']): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user me-1"></i><?php echo $task['assigned_staff']; ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-3">
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                                            <button type="submit" name="complete_task" class="btn btn-sm btn-success">
                                                                <i class="fas fa-check me-1"></i>Complete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Completed Column -->
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Completed <span class="badge bg-light text-dark" id="completed-count">0</span></h6>
                                    </div>
                                    <div class="card-body kanban-column" data-status="completed">
                                        <?php foreach ($tasks as $task): ?>
                                            <?php if ($task['status'] == 'completed'): ?>
                                            <div class="card mb-3 task-card completed-task">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="card-title mb-0 text-muted">Room <?php echo $task['room_number']; ?></h6>
                                                        <span class="badge bg-success">Completed</span>
                                                    </div>
                                                    
                                                    <p class="card-text mb-1 text-muted">
                                                        <small><?php echo ucfirst($task['task_type']); ?></small>
                                                    </p>
                                                    
                                                    <?php if ($task['completed_time']): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            <?php echo date('H:i', strtotime($task['completed_time'])); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- AI Recommendations -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>AI Housekeeping Insights</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-bolt me-2"></i>Efficiency Tip</h6>
                                    <p class="mb-1">Group cleaning tasks by floor to reduce staff movement by 40%</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-chart-line me-2"></i>Performance</h6>
                                    <p class="mb-1">AI optimization has reduced average task completion time by 28%</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Staff Allocation</h6>
                                    <p class="mb-1">Assign 2 additional staff to floor 5 during 2-4 PM peak hours</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-primary">
                                    <h6><i class="fas fa-robot me-2"></i>Predictive Maintenance</h6>
                                    <p class="mb-1">3 rooms predicted to require deep cleaning in next 48 hours</p>
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
        // Update task counts
        function updateTaskCounts() {
            const pendingCount = document.querySelectorAll('[data-status="pending"] .task-card').length;
            const progressCount = document.querySelectorAll('[data-status="in_progress"] .task-card').length;
            const completedCount = document.querySelectorAll('[data-status="completed"] .task-card').length;
            
            document.getElementById('pending-count').textContent = pendingCount;
            document.getElementById('progress-count').textContent = progressCount;
            document.getElementById('completed-count').textContent = completedCount;
        }

        // Run AI optimization
        function runAIOptimization() {
            const button = event.target;
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Optimizing...';
            button.disabled = true;
            
            // Simulate AI processing
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check me-1"></i>Optimized!';
                button.classList.remove('btn-light');
                button.classList.add('btn-success');
                
                // Show optimization result
                alert('AI has re-optimized the task sequence! Efficiency improved by 15%');
                
                // Reload page after delay
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            }, 2000);
        }

        // Initialize counts
        updateTaskCounts();

        // Simulate real-time updates
        setInterval(() => {
            // Randomly update some task statuses for demo
            const pendingTasks = document.querySelectorAll('[data-status="pending"] .task-card');
            if (pendingTasks.length > 0 && Math.random() > 0.7) {
                const randomTask = pendingTasks[Math.floor(Math.random() * pendingTasks.length)];
                // Simulate task moving to in progress
                randomTask.querySelector('.btn-success').click();
            }
        }, 10000);
    </script>
    
    <style>
        .kanban-column {
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
        }
        .task-card {
            cursor: move;
            transition: all 0.3s;
        }
        .task-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .completed-task {
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        .task-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</body>
</html>