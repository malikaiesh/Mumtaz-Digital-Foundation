<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $payment_id = (int)($_POST['payment_id'] ?? 0);
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE payments SET status = 'approved', approved_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$payment_id]);
        
        $stmt = $conn->prepare("SELECT enrollment_id FROM payments WHERE id = ?");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            $stmt = $conn->prepare("UPDATE enrollments SET payment_status = 'approved' WHERE id = ?");
            $stmt->execute([$payment['enrollment_id']]);
            
            $stmt = $conn->prepare("SELECT e.user_id, c.title, c.id as course_id FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.id = ?");
            $stmt->execute([$payment['enrollment_id']]);
            $enrollment = $stmt->fetch();
            
            if ($enrollment) {
                $conn->prepare("UPDATE courses SET total_students = total_students + 1 WHERE id = ?")
                     ->execute([$enrollment['course_id']]);
                
                $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')")
                     ->execute([$enrollment['user_id'], 'Payment Approved!', 'Your payment for ' . $enrollment['title'] . ' has been approved. Start learning now!']);
            }
        }
        
        $success = 'Payment approved successfully!';
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE payments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$payment_id]);
        
        $stmt = $conn->prepare("SELECT enrollment_id FROM payments WHERE id = ?");
        $stmt->execute([$payment_id]);
        $payment = $stmt->fetch();
        
        if ($payment) {
            $stmt = $conn->prepare("UPDATE enrollments SET payment_status = 'rejected' WHERE id = ?");
            $stmt->execute([$payment['enrollment_id']]);
        }
        
        $success = 'Payment rejected.';
    }
}

$stmt = $conn->query("SELECT p.*, u.name as student_name, u.email, c.title as course_title 
                      FROM payments p 
                      JOIN users u ON p.user_id = u.id 
                      JOIN enrollments e ON p.enrollment_id = e.id
                      JOIN courses c ON e.course_id = c.id 
                      ORDER BY p.created_at DESC");
$payments = $stmt->fetchAll();

$page_title = 'Manage Payments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: #1e293b; color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-content { flex: 1; margin-left: 260px; background: #f1f5f9; min-height: 100vh; }
        .admin-sidebar .nav-link { color: #94a3b8; padding: 0.75rem 1.5rem; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(99, 102, 241, 0.2); color: white; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="p-4">
                <h5 class="fw-bold text-white mb-0"><i class="fas fa-graduation-cap me-2"></i>MDF Admin</h5>
            </div>
            <nav class="nav flex-column">
                <a href="/admin/" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="/admin/courses.php" class="nav-link"><i class="fas fa-book me-2"></i>Courses</a>
                <a href="/admin/students.php" class="nav-link"><i class="fas fa-users me-2"></i>Students</a>
                <a href="/admin/enrollments.php" class="nav-link"><i class="fas fa-user-graduate me-2"></i>Enrollments</a>
                <a href="/admin/payments.php" class="nav-link active"><i class="fas fa-credit-card me-2"></i>Payments</a>
                <a href="/admin/categories.php" class="nav-link"><i class="fas fa-folder me-2"></i>Categories</a>
                <hr class="border-secondary my-2">
                <a href="/" class="nav-link"><i class="fas fa-home me-2"></i>View Website</a>
                <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="p-4">
                <h4 class="fw-bold mb-4">Manage Payments</h4>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Transaction ID</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($payment['student_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($payment['course_title']); ?></td>
                                        <td><strong><?php echo formatPrice($payment['amount']); ?></strong></td>
                                        <td><?php echo ucfirst($payment['payment_method'] ?? 'N/A'); ?></td>
                                        <td><code><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></code></td>
                                        <td>
                                            <span class="badge bg-<?php echo $payment['status'] === 'approved' ? 'success' : ($payment['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($payment['created_at'])); ?></td>
                                        <td>
                                            <?php if ($payment['status'] === 'pending'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                            </form>
                                            <?php else: ?>
                                            <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($payments)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No payments found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
