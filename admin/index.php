<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$stats = [];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'");
$stats['students'] = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM courses WHERE is_published = TRUE");
$stats['courses'] = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE payment_status = 'approved'");
$stats['enrollments'] = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'approved'");
$stats['revenue'] = $stmt->fetch()['total'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
$stats['pending_payments'] = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT e.*, u.name as student_name, u.email, c.title as course_title 
                      FROM enrollments e 
                      JOIN users u ON e.user_id = u.id 
                      JOIN courses c ON e.course_id = c.id 
                      ORDER BY e.enrolled_at DESC LIMIT 10");
$recent_enrollments = $stmt->fetchAll();

$stmt = $conn->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5");
$recent_students = $stmt->fetchAll();

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Mumtaz Digital Foundation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: #1e293b; color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-content { flex: 1; margin-left: 260px; background: #f1f5f9; min-height: 100vh; }
        .admin-sidebar .nav-link { color: #94a3b8; padding: 0.75rem 1.5rem; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: rgba(99, 102, 241, 0.2); color: white; }
        .admin-sidebar .nav-link i { width: 20px; }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="p-4">
                <h5 class="fw-bold text-white mb-0"><i class="fas fa-graduation-cap me-2"></i>MDF Admin</h5>
            </div>
            <nav class="nav flex-column">
                <a href="/admin/" class="nav-link active"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                <a href="/admin/courses.php" class="nav-link"><i class="fas fa-book me-2"></i>Courses</a>
                <a href="/admin/students.php" class="nav-link"><i class="fas fa-users me-2"></i>Students</a>
                <a href="/admin/enrollments.php" class="nav-link"><i class="fas fa-user-graduate me-2"></i>Enrollments</a>
                <a href="/admin/payments.php" class="nav-link"><i class="fas fa-credit-card me-2"></i>Payments</a>
                <a href="/admin/categories.php" class="nav-link"><i class="fas fa-folder me-2"></i>Categories</a>
                <hr class="border-secondary my-2">
                <a href="/" class="nav-link"><i class="fas fa-home me-2"></i>View Website</a>
                <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Dashboard</h4>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?php echo $stats['students']; ?></h3>
                                        <p class="text-muted mb-0">Total Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                                        <i class="fas fa-book fa-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?php echo $stats['courses']; ?></h3>
                                        <p class="text-muted mb-0">Active Courses</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                                        <i class="fas fa-user-graduate fa-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?php echo $stats['enrollments']; ?></h3>
                                        <p class="text-muted mb-0">Enrollments</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                                        <i class="fas fa-money-bill fa-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0 fw-bold"><?php echo formatPrice($stats['revenue']); ?></h3>
                                        <p class="text-muted mb-0">Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($stats['pending_payments'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    You have <strong><?php echo $stats['pending_payments']; ?></strong> pending payment(s) to review. 
                    <a href="/admin/payments.php" class="alert-link">Review now</a>
                </div>
                <?php endif; ?>
                
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0">Recent Enrollments</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Student</th>
                                                <th>Course</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_enrollments as $enrollment): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($enrollment['student_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($enrollment['email']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $enrollment['payment_status'] === 'approved' ? 'success' : ($enrollment['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($enrollment['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0">New Students</h6>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($recent_students as $student): ?>
                                    <li class="list-group-item d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                            <small class="text-muted"><?php echo timeAgo($student['created_at']); ?></small>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
