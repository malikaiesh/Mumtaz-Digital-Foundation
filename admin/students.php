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
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'toggle_active') {
        $stmt = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$user_id]);
        $success = 'Student status updated!';
    } elseif ($action === 'delete_user') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
        $stmt->execute([$user_id]);
        $success = 'Student deleted!';
    }
}

$stmt = $conn->query("SELECT u.*, 
                      (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id AND payment_status = 'approved') as enrolled_courses,
                      (SELECT COUNT(*) FROM certificates WHERE user_id = u.id) as certificates
                      FROM users u WHERE u.role = 'student' ORDER BY u.created_at DESC");
$students = $stmt->fetchAll();

$page_title = 'Manage Students';
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
                <a href="/admin/students.php" class="nav-link active"><i class="fas fa-users me-2"></i>Students</a>
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
                <h4 class="fw-bold mb-4">Manage Students</h4>
                
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
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Courses</th>
                                        <th>Certificates</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                                </div>
                                                <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $student['enrolled_courses']; ?></span></td>
                                        <td><span class="badge bg-success"><?php echo $student['certificates']; ?></span></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_active">
                                                <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $student['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $student['is_active'] ? 'Active' : 'Blocked'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this student?');">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?php echo $student['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">No students found</td>
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
