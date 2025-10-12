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

// Handle new guest
if ($_POST && isset($_POST['add_guest'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $preferences = implode(',', $_POST['preferences'] ?? []);
    
    $query = "INSERT INTO guests (first_name, last_name, email, phone, address, preferences) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($query);
    if ($stmt->execute([$first_name, $last_name, $email, $phone, $address, $preferences])) {
        $_SESSION['success'] = "Guest added successfully!";
    }
}

// Handle send offer
if ($_POST && isset($_POST['send_offer'])) {
    $guest_id = $_POST['guest_id'];
    $offer_type = $_POST['offer_type'];
    $discount = $_POST['discount'];
    $message = $_POST['message'];
    
    // Get guest details for personalization
    $guest_query = "SELECT first_name, last_name, email, preferences FROM guests WHERE id = ?";
    $stmt = $db->prepare($guest_query);
    $stmt->execute([$guest_id]);
    $guest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // AI-powered offer personalization
    $personalized_offer = $ai_model->personalizeOffer($guest, $offer_type, $discount);
    
    // Log the offer (in real implementation, this would send email/SMS)
    $log_query = "INSERT INTO guest_offers (guest_id, offer_type, discount, message, personalized_message, sent_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
    $db->prepare($log_query)->execute([$guest_id, $offer_type, $discount, $message, $personalized_offer]);
    
    $_SESSION['success'] = "AI-personalized offer sent to " . $guest['first_name'] . " " . $guest['last_name'];
}

// Get filter parameters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query with filters
// C:\xampp\htdocs\hotel_system\guests.php

// ... (Lines 46-52 remain the same)

// Build base query
$query = "SELECT g.*, 
          COUNT(r.id) as total_stays,
          AVG(r.total_amount) as avg_spend,
          MAX(r.created_at) as last_visit
          FROM guests g 
          LEFT JOIN reservations r ON g.id = r.guest_id 
          WHERE 1=1"; // Start with WHERE for non-aggregate filters

$params = [];

// 1. Apply non-aggregate search filters (Belong in WHERE)
if ($search) {
    $query .= " AND (g.first_name LIKE ? OR g.last_name LIKE ? OR g.email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

// 2. Add GROUP BY clause (Must come before HAVING)
$query .= " GROUP BY g.id"; 

// 3. Apply aggregate filters (Must go into a HAVING clause)
if ($filter === 'vip') {
    // FIX: Changed from WHERE to HAVING
    $query .= " HAVING COUNT(r.id) >= 3";
} elseif ($filter === 'repeat') {
    // FIX: Changed from WHERE to HAVING
    $query .= " HAVING COUNT(r.id) > 1";
} elseif ($filter === 'new') {
    // FIX: Changed from WHERE to HAVING
    $query .= " HAVING COUNT(r.id) = 0";
}

// 4. Add ORDER BY clause
$query .= " ORDER BY g.created_at DESC";

$stmt = $db->prepare($query);
// Line 90 (Error line) will now execute the corrected SQL.
$stmt->execute($params); 
$guests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ... (Rest of the code)
// Calculate guest value scores and AI insights
foreach ($guests as &$guest) {
    $guest['loyalty_score'] = min(100, $guest['total_stays'] * 10 + $guest['loyalty_points']);
    $guest['value_tier'] = $guest['avg_spend'] > 200 ? 'Premium' : 
                          ($guest['avg_spend'] > 100 ? 'Standard' : 'Budget');
    
    // AI-generated insights
    $guest['ai_insights'] = $ai_model->generateGuestInsights($guest);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guests - AI Hotel Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include "navbar.php"; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-4">
                <!-- Add Guest Form -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Add New Guest</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" name="first_name" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Preferences</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="non_smoking">
                                    <label class="form-check-label">Non-smoking room</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="high_floor">
                                    <label class="form-check-label">High floor</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="late_checkout">
                                    <label class="form-check-label">Late checkout</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="extra_pillows">
                                    <label class="form-check-label">Extra pillows</label>
                                </div>
                            </div>
                            
                            <button type="submit" name="add_guest" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>Add Guest
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Send Offer Form -->
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-gift me-2"></i>Send AI-Personalized Offer</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Select Guest</label>
                                <select name="guest_id" class="form-select" required>
                                    <option value="">Choose Guest</option>
                                    <?php foreach ($guests as $guest): ?>
                                        <option value="<?php echo $guest['id']; ?>">
                                            <?php echo $guest['first_name'] . ' ' . $guest['last_name']; ?>
                                            <?php if ($guest['total_stays'] >= 3): ?> (VIP) <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Offer Type</label>
                                <select name="offer_type" class="form-select" required>
                                    <option value="room_upgrade">Room Upgrade</option>
                                    <option value="discount">Special Discount</option>
                                    <option value="package">Stay Package</option>
                                    <option value="loyalty">Loyalty Bonus</option>
                                    <option value="weekend">Weekend Special</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Discount Percentage</label>
                                <input type="range" name="discount" class="form-range" min="0" max="50" step="5" value="15">
                                <div class="d-flex justify-content-between">
                                    <small>0%</small>
                                    <small id="discount-value">15%</small>
                                    <small>50%</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Personal Message</label>
                                <textarea name="message" class="form-control" rows="3" 
                                          placeholder="AI will enhance this message with personalization..."></textarea>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-robot me-2"></i>
                                <strong>AI Personalization:</strong> 
                                <span id="ai-offer-insight">Select a guest to see AI personalization preview</span>
                            </div>
                            
                            <button type="submit" name="send_offer" class="btn btn-success w-100">
                                <i class="fas fa-paper-plane me-2"></i>Send AI-Enhanced Offer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <!-- Guests Filter and Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form method="GET" class="d-flex gap-2">
                                    <input type="text" name="search" class="form-control" placeholder="Search guests..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <select name="filter" class="form-select" onchange="this.form.submit()">
                                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Guests</option>
                                        <option value="vip" <?php echo $filter === 'vip' ? 'selected' : ''; ?>>VIP Guests (3+ stays)</option>
                                        <option value="repeat" <?php echo $filter === 'repeat' ? 'selected' : ''; ?>>Repeat Guests</option>
                                        <option value="new" <?php echo $filter === 'new' ? 'selected' : ''; ?>>New Guests</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-secondary"><?php echo count($guests); ?> guests found</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guests List -->
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Guest Management</h5>
                        <div>
                            <span class="badge bg-light text-dark"><?php echo count($guests); ?> guests</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Guest</th>
                                        <th>Contact</th>
                                        <th>Stays</th>
                                        <th>Avg Spend</th>
                                        <th>Loyalty</th>
                                        <th>AI Insights</th>
                                        <th>Last Visit</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($guests as $guest): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $guest['first_name'] . ' ' . $guest['last_name']; ?></strong>
                                            <?php if ($guest['total_stays'] >= 3): ?>
                                                <i class="fas fa-crown text-warning ms-1" title="VIP Guest"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo $guest['email']; ?><br>
                                            <?php echo $guest['phone']; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $guest['total_stays']; ?></span>
                                        </td>
                                        <td>
                                            $<?php echo $guest['avg_spend'] ? round($guest['avg_spend'], 2) : '0'; ?>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: <?php echo $guest['loyalty_score']; ?>%">
                                                    <?php echo $guest['loyalty_score']; ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted" title="<?php echo $guest['ai_insights']; ?>">
                                                <i class="fas fa-robot me-1"></i>
                                                <?php echo substr($guest['ai_insights'], 0, 50) . '...'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <small>
                                                <?php echo $guest['last_visit'] ? 
                                                    date('M j, Y', strtotime($guest['last_visit'])) : 'Never'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="viewGuestDetails(<?php echo $guest['id']; ?>)"
                                                        data-bs-toggle="tooltip" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success"
                                                        onclick="quickOffer(<?php echo $guest['id']; ?>, '<?php echo $guest['first_name']; ?>')"
                                                        data-bs-toggle="tooltip" title="Send Offer">
                                                    <i class="fas fa-gift"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-info"
                                                        onclick="showAIInsights(<?php echo $guest['id']; ?>)"
                                                        data-bs-toggle="tooltip" title="AI Insights">
                                                    <i class="fas fa-robot"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- AI Guest Analytics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>AI Guest Analytics</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-primary" id="total-guests"><?php echo count($guests); ?></h4>
                                    <small class="text-muted">Total Guests</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-success" id="repeat-rate">
                                        <?php 
                                        $repeat_guests = array_filter($guests, function($g) { return $g['total_stays'] > 1; });
                                        echo count($repeat_guests) > 0 ? round((count($repeat_guests) / count($guests)) * 100) : 0;
                                        ?>%
                                    </h4>
                                    <small class="text-muted">Repeat Rate</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-warning" id="vip-count">
                                        <?php 
                                        $vip_guests = array_filter($guests, function($g) { return $g['total_stays'] >= 3; });
                                        echo count($vip_guests);
                                        ?>
                                    </h4>
                                    <small class="text-muted">VIP Guests</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3">
                                    <h4 class="text-danger" id="avg-loyalty">
                                        <?php 
                                        $avg_loyalty = array_sum(array_column($guests, 'loyalty_score')) / count($guests);
                                        echo round($avg_loyalty);
                                        ?>%
                                    </h4>
                                    <small class="text-muted">Avg Loyalty</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Insights Modal -->
    <div class="modal fade" id="aiInsightsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-robot me-2"></i>AI Guest Insights</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="aiInsightsContent">
                    <!-- AI insights will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update discount value display
        document.querySelector('input[name="discount"]').addEventListener('input', function() {
            document.getElementById('discount-value').textContent = this.value + '%';
        });

        // Quick offer function
        function quickOffer(guestId, firstName) {
            document.querySelector('select[name="guest_id"]').value = guestId;
            document.getElementById('ai-offer-insight').textContent = 
                `AI will personalize offer for ${firstName} based on their stay history and preferences.`;
            
            // Scroll to offer form
            document.querySelector('.card-header.bg-success').scrollIntoView({
                behavior: 'smooth'
            });
        }

        // Show AI insights
        function showAIInsights(guestId) {
            // Simulate AI insights loading
            const insights = [
                "High-value guest with premium spending patterns",
                "Prefers weekend stays with family amenities",
                "Responds well to personalized room upgrade offers",
                "Potential for extended stay packages",
                "Loyalty program engagement: High"
            ];
            
            document.getElementById('aiInsightsContent').innerHTML = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>AI-Generated Insights</h6>
                    <ul>
                        ${insights.map(insight => `<li>${insight}</li>`).join('')}
                    </ul>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Recommended Offers</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Weekend family package (25% discount)</li>
                                    <li class="list-group-item">Room upgrade with late checkout</li>
                                    <li class="list-group-item">Spa package bundle</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6>Engagement Strategy</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">Send personalized welcome back email</li>
                                    <li class="list-group-item">Offer loyalty bonus points</li>
                                    <li class="list-group-item">Invite to VIP guest program</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('aiInsightsModal')).show();
        }

        // View guest details
        function viewGuestDetails(guestId) {
            alert('Guest details view would open for ID: ' + guestId);
            // In full implementation, this would open a detailed guest profile modal
        }

        // Simulate real-time analytics updates
        setInterval(() => {
            const currentGuests = parseInt(document.getElementById('total-guests').textContent);
            document.getElementById('total-guests').textContent = currentGuests + (Math.random() > 0.7 ? 1 : 0);
        }, 10000);
    </script>
</body>
</html>