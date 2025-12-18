<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT e.*, c.title, c.slug, c.thumbnail, c.total_lessons, c.duration, cat.name as category_name 
                        FROM enrollments e 
                        LEFT JOIN courses c ON e.course_id = c.id 
                        LEFT JOIN categories cat ON c.category_id = cat.id
                        WHERE e.user_id = ? AND e.payment_status = 'approved' 
                        ORDER BY e.enrolled_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$enrollments = $stmt->fetchAll();

$page_title = 'My Courses';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">My Courses</h3>
            <a href="/pages/courses.php" class="btn btn-primary">Browse More Courses</a>
        </div>
        
        <?php if (empty($enrollments)): ?>
        <div class="text-center py-5">
            <i class="fas fa-book-open fa-4x text-muted mb-3"></i>
            <h4>No courses enrolled yet</h4>
            <p class="text-muted">Start your learning journey by enrolling in a course</p>
            <a href="/pages/courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($enrollments as $enrollment): ?>
            <div class="col-md-6 col-lg-4">
                <div class="course-card h-100">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=200&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($enrollment['title']); ?>">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($enrollment['category_name']); ?></span>
                        <h5 class="course-title"><?php echo htmlspecialchars($enrollment['title']); ?></h5>
                        <div class="course-meta mb-3">
                            <span><i class="fas fa-clock me-1"></i><?php echo $enrollment['duration'] ?? 'Self-paced'; ?></span>
                            <span><i class="fas fa-play-circle me-1"></i><?php echo $enrollment['total_lessons']; ?> Lessons</span>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small>Progress</small>
                                <small class="fw-bold"><?php echo $enrollment['progress']; ?>%</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" style="width: <?php echo $enrollment['progress']; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0">
                        <a href="/pages/learn.php?course=<?php echo $enrollment['slug']; ?>" class="btn btn-primary w-100">
                            <?php echo $enrollment['progress'] > 0 ? 'Continue Learning' : 'Start Learning'; ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
