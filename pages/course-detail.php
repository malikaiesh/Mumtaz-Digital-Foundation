<?php
require_once __DIR__ . '/../config/init.php';

$db = new Database();
$conn = $db->getConnection();

$slug = sanitize($_GET['slug'] ?? '');
if (!$slug) {
    redirect('/pages/courses.php');
}

$stmt = $conn->prepare("SELECT c.*, cat.name as category_name, u.name as instructor_name, u.bio as instructor_bio, u.avatar as instructor_avatar 
                        FROM courses c 
                        LEFT JOIN categories cat ON c.category_id = cat.id 
                        LEFT JOIN users u ON c.instructor_id = u.id 
                        WHERE c.slug = ? AND c.is_published = TRUE");
$stmt->execute([$slug]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php');
}

$stmt = $conn->prepare("SELECT m.*, (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as lesson_count 
                        FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order");
$stmt->execute([$course['id']]);
$modules = $stmt->fetchAll();

foreach ($modules as &$module) {
    $stmt = $conn->prepare("SELECT * FROM lessons WHERE module_id = ? ORDER BY sort_order");
    $stmt->execute([$module['id']]);
    $module['lessons'] = $stmt->fetchAll();
}

$is_enrolled = false;
$enrollment = null;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND payment_status = 'approved'");
    $stmt->execute([$_SESSION['user_id'], $course['id']]);
    $enrollment = $stmt->fetch();
    $is_enrolled = (bool)$enrollment;
}

$stmt = $conn->prepare("SELECT r.*, u.name as user_name FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.course_id = ? AND r.is_approved = TRUE ORDER BY r.created_at DESC LIMIT 5");
$stmt->execute([$course['id']]);
$reviews = $stmt->fetchAll();

$page_title = $course['title'];
$meta_description = $course['short_description'];
include __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="/pages/courses.php">Courses</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['title']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<section class="bg-dark text-white py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['category_name']); ?></span>
                    <span class="level-badge level-<?php echo $course['level']; ?>"><?php echo ucfirst($course['level']); ?></span>
                    <?php if ($course['is_featured']): ?>
                    <span class="badge bg-warning">Featured</span>
                    <?php endif; ?>
                </div>
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h1>
                <p class="lead mb-4"><?php echo htmlspecialchars($course['short_description']); ?></p>
                <div class="d-flex flex-wrap gap-4 mb-4">
                    <span><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($course['instructor_name']); ?></span>
                    <span><i class="fas fa-clock me-2"></i><?php echo $course['duration'] ?? 'Self-paced'; ?></span>
                    <span><i class="fas fa-play-circle me-2"></i><?php echo $course['total_lessons']; ?> Lessons</span>
                    <span><i class="fas fa-users me-2"></i><?php echo $course['total_students']; ?> Students</span>
                    <span><i class="fas fa-globe me-2"></i><?php echo $course['language'] ?? 'English'; ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">About This Course</h4>
                        <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                    </div>
                </div>
                
                <?php if ($course['outcomes']): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-check-circle text-success me-2"></i>What You'll Learn</h4>
                        <div class="row">
                            <?php foreach (explode('|', $course['outcomes']) as $outcome): ?>
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars(trim($outcome)); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($course['skills']): ?>
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-tools text-primary me-2"></i>Skills You'll Gain</h4>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (explode('|', $course['skills']) as $skill): ?>
                            <span class="badge bg-light text-dark px-3 py-2"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-list me-2"></i>Course Curriculum</h4>
                        <div class="accordion" id="curriculumAccordion">
                            <?php foreach ($modules as $index => $module): ?>
                            <div class="accordion-item border-0 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#module<?php echo $module['id']; ?>">
                                        <div class="d-flex justify-content-between w-100 me-3">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($module['title']); ?></span>
                                            <span class="text-muted small"><?php echo count($module['lessons']); ?> lessons</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="module<?php echo $module['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#curriculumAccordion">
                                    <div class="accordion-body p-0">
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($module['lessons'] as $lesson): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <div>
                                                    <i class="fas fa-play-circle text-primary me-2"></i>
                                                    <?php echo htmlspecialchars($lesson['title']); ?>
                                                    <?php if ($lesson['is_free']): ?>
                                                    <span class="badge bg-success ms-2">Free Preview</span>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="text-muted small"><?php echo $lesson['duration']; ?> min</span>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3"><i class="fas fa-chalkboard-teacher me-2"></i>Your Instructor</h4>
                        <div class="d-flex align-items-start">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo strtoupper(substr($course['instructor_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($course['instructor_name']); ?></h5>
                                <p class="text-muted mb-2">Professional Instructor</p>
                                <p class="mb-0"><?php echo htmlspecialchars($course['instructor_bio'] ?? 'Experienced instructor dedicated to helping students succeed.'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&h=250&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <?php if ($course['price'] == 0): ?>
                            <h2 class="text-success fw-bold">Free</h2>
                            <?php else: ?>
                            <h2 class="fw-bold"><?php echo formatPrice($course['price']); ?></h2>
                            <?php if ($course['discount_price']): ?>
                            <span class="text-muted text-decoration-line-through"><?php echo formatPrice($course['discount_price']); ?></span>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($is_enrolled): ?>
                        <a href="/pages/learn.php?course=<?php echo $course['slug']; ?>" class="btn btn-success btn-lg w-100 mb-3">
                            <i class="fas fa-play me-2"></i>Continue Learning
                        </a>
                        <div class="text-center">
                            <div class="progress mb-2" style="height: 10px;">
                                <div class="progress-bar" style="width: <?php echo $enrollment['progress']; ?>%"></div>
                            </div>
                            <small class="text-muted"><?php echo $enrollment['progress']; ?>% Complete</small>
                        </div>
                        <?php elseif (isLoggedIn()): ?>
                        <a href="/pages/enroll.php?course=<?php echo $course['slug']; ?>" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-shopping-cart me-2"></i>Enroll Now
                        </a>
                        <?php else: ?>
                        <a href="/pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login to Enroll
                        </a>
                        <?php endif; ?>
                        
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="fas fa-infinity text-primary me-2"></i>Lifetime access</li>
                            <li class="mb-2"><i class="fas fa-mobile-alt text-primary me-2"></i>Access on mobile and desktop</li>
                            <li class="mb-2"><i class="fas fa-certificate text-primary me-2"></i>Certificate of completion</li>
                            <li class="mb-2"><i class="fas fa-download text-primary me-2"></i>Downloadable resources</li>
                            <li class="mb-0"><i class="fas fa-headset text-primary me-2"></i>Full support</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
