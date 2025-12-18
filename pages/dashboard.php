<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $conn->prepare("SELECT e.*, c.title, c.slug, c.thumbnail, c.total_lessons 
                        FROM enrollments e 
                        LEFT JOIN courses c ON e.course_id = c.id 
                        WHERE e.user_id = ? AND e.payment_status = 'approved' 
                        ORDER BY e.enrolled_at DESC");
$stmt->execute([$user_id]);
$enrollments = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE user_id = ? AND payment_status = 'approved'");
$stmt->execute([$user_id]);
$enrolled_count = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM enrollments WHERE user_id = ? AND payment_status = 'approved' AND progress = 100");
$stmt->execute([$user_id]);
$completed_count = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM certificates WHERE user_id = ?");
$stmt->execute([$user_id]);
$certificate_count = $stmt->fetch()['count'];

$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

$page_title = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="dashboard-sidebar">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a href="/pages/dashboard.php" class="nav-link active"><i class="fas fa-home"></i> Dashboard</a>
                        <a href="/pages/my-courses.php" class="nav-link"><i class="fas fa-book"></i> My Courses</a>
                        <a href="/pages/certificates.php" class="nav-link"><i class="fas fa-certificate"></i> Certificates</a>
                        <a href="/pages/profile.php" class="nav-link"><i class="fas fa-user"></i> Profile</a>
                        <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            
            <div class="col-lg-9">
                <h3 class="fw-bold mb-4">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h3>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-book-open"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $enrolled_count; ?></h3>
                                    <p class="text-muted mb-0">Enrolled Courses</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $completed_count; ?></h3>
                                    <p class="text-muted mb-0">Completed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="d-flex align-items-center">
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-certificate"></i>
                                </div>
                                <div class="ms-3">
                                    <h3 class="mb-0 fw-bold"><?php echo $certificate_count; ?></h3>
                                    <p class="text-muted mb-0">Certificates</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Continue Learning</h5>
                            <a href="/pages/my-courses.php" class="text-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($enrollments)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <h5>No courses yet</h5>
                            <p class="text-muted">Start your learning journey by enrolling in a course</p>
                            <a href="/pages/courses.php" class="btn btn-primary">Browse Courses</a>
                        </div>
                        <?php else: ?>
                        <div class="row g-3">
                            <?php foreach (array_slice($enrollments, 0, 3) as $enrollment): ?>
                            <div class="col-md-4">
                                <div class="card h-100 border">
                                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=300&h=150&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($enrollment['title']); ?>">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($enrollment['title']); ?></h6>
                                        <div class="progress mb-2" style="height: 6px;">
                                            <div class="progress-bar" style="width: <?php echo $enrollment['progress']; ?>%"></div>
                                        </div>
                                        <small class="text-muted"><?php echo $enrollment['progress']; ?>% Complete</small>
                                    </div>
                                    <div class="card-footer bg-white border-0">
                                        <a href="/pages/learn.php?course=<?php echo $enrollment['slug']; ?>" class="btn btn-primary btn-sm w-100">Continue</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Recent Notifications</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($notifications)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No notifications yet</p>
                        </div>
                        <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                            <li class="list-group-item py-3 <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>">
                                <div class="d-flex align-items-start">
                                    <div class="bg-<?php echo $notification['type'] === 'success' ? 'success' : ($notification['type'] === 'warning' ? 'warning' : 'primary'); ?> rounded-circle p-2 me-3">
                                        <i class="fas fa-bell text-white" style="width: 16px;"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <small class="text-muted"><?php echo timeAgo($notification['created_at']); ?></small>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
