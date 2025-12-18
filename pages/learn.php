<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$slug = sanitize($_GET['course'] ?? '');
$lesson_id = (int)($_GET['lesson'] ?? 0);

$stmt = $conn->prepare("SELECT c.*, e.id as enrollment_id, e.progress, e.completed_lessons 
                        FROM courses c 
                        JOIN enrollments e ON c.id = e.course_id 
                        WHERE c.slug = ? AND e.user_id = ? AND e.payment_status = 'approved'");
$stmt->execute([$slug, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php');
}

$stmt = $conn->prepare("SELECT m.* FROM modules m WHERE m.course_id = ? ORDER BY m.sort_order");
$stmt->execute([$course['id']]);
$modules = $stmt->fetchAll();

foreach ($modules as &$module) {
    $stmt = $conn->prepare("SELECT l.*, 
                            (SELECT is_completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as is_completed 
                            FROM lessons l WHERE l.module_id = ? ORDER BY l.sort_order");
    $stmt->execute([$_SESSION['user_id'], $module['id']]);
    $module['lessons'] = $stmt->fetchAll();
}

$current_lesson = null;
if ($lesson_id) {
    $stmt = $conn->prepare("SELECT l.*, m.title as module_title FROM lessons l 
                            JOIN modules m ON l.module_id = m.id 
                            WHERE l.id = ? AND m.course_id = ?");
    $stmt->execute([$lesson_id, $course['id']]);
    $current_lesson = $stmt->fetch();
} else {
    foreach ($modules as $module) {
        if (!empty($module['lessons'])) {
            $current_lesson = $module['lessons'][0];
            $current_lesson['module_title'] = $module['title'];
            break;
        }
    }
}

$total_lessons = 0;
$completed_lessons = 0;
foreach ($modules as $module) {
    foreach ($module['lessons'] as $lesson) {
        $total_lessons++;
        if ($lesson['is_completed']) {
            $completed_lessons++;
        }
    }
}
$progress = $total_lessons > 0 ? round(($completed_lessons / $total_lessons) * 100) : 0;

$page_title = $current_lesson ? $current_lesson['title'] : $course['title'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4" style="background: #f8fafc;">
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($course['title']); ?></h6>
                    <div class="d-flex align-items-center">
                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                            <div class="progress-bar course-progress-bar" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <small class="progress-text fw-bold"><?php echo $progress; ?>%</small>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 70vh; overflow-y: auto;">
                    <div class="accordion accordion-flush" id="lessonAccordion">
                        <?php foreach ($modules as $index => $module): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?> py-3" type="button" data-bs-toggle="collapse" data-bs-target="#moduleCollapse<?php echo $module['id']; ?>">
                                    <small class="fw-semibold"><?php echo htmlspecialchars($module['title']); ?></small>
                                </button>
                            </h2>
                            <div id="moduleCollapse<?php echo $module['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-bs-parent="#lessonAccordion">
                                <div class="list-group list-group-flush">
                                    <?php foreach ($module['lessons'] as $lesson): ?>
                                    <a href="/pages/learn.php?course=<?php echo $slug; ?>&lesson=<?php echo $lesson['id']; ?>" 
                                       class="list-group-item list-group-item-action d-flex align-items-center py-2 <?php echo ($current_lesson && $current_lesson['id'] == $lesson['id']) ? 'active' : ''; ?>">
                                        <div class="me-2">
                                            <?php if ($lesson['is_completed']): ?>
                                            <i class="fas fa-check-circle text-success"></i>
                                            <?php else: ?>
                                            <i class="far fa-circle text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <small><?php echo htmlspecialchars($lesson['title']); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo $lesson['duration']; ?>m</small>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-9">
            <?php if ($current_lesson): ?>
            <div class="card shadow-sm border-0 mb-4">
                <?php if ($current_lesson['video_url']): ?>
                <div class="ratio ratio-16x9 bg-dark">
                    <iframe src="<?php echo htmlspecialchars($current_lesson['video_url']); ?>" title="<?php echo htmlspecialchars($current_lesson['title']); ?>" allowfullscreen></iframe>
                </div>
                <?php endif; ?>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <small class="text-muted"><?php echo htmlspecialchars($current_lesson['module_title']); ?></small>
                            <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($current_lesson['title']); ?></h4>
                        </div>
                        <button class="btn btn-outline-success mark-complete" data-lesson-id="<?php echo $current_lesson['id']; ?>" <?php echo $current_lesson['is_completed'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-check me-1"></i> <?php echo $current_lesson['is_completed'] ? 'Completed' : 'Mark Complete'; ?>
                        </button>
                    </div>
                    
                    <?php if ($current_lesson['content']): ?>
                    <div class="lesson-content mt-4">
                        <?php echo nl2br(htmlspecialchars($current_lesson['content'])); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($current_lesson['attachments']): ?>
                    <div class="mt-4">
                        <h6 class="fw-bold">Attachments</h6>
                        <div class="list-group">
                            <?php foreach (json_decode($current_lesson['attachments'], true) ?? [] as $attachment): ?>
                            <a href="<?php echo htmlspecialchars($attachment); ?>" class="list-group-item list-group-item-action" download>
                                <i class="fas fa-download me-2"></i><?php echo basename($attachment); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <?php
                $prev_lesson = null;
                $next_lesson = null;
                $found_current = false;
                foreach ($modules as $module) {
                    foreach ($module['lessons'] as $lesson) {
                        if ($found_current && !$next_lesson) {
                            $next_lesson = $lesson;
                        }
                        if ($lesson['id'] == $current_lesson['id']) {
                            $found_current = true;
                        }
                        if (!$found_current) {
                            $prev_lesson = $lesson;
                        }
                    }
                }
                ?>
                
                <?php if ($prev_lesson): ?>
                <a href="/pages/learn.php?course=<?php echo $slug; ?>&lesson=<?php echo $prev_lesson['id']; ?>" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Previous Lesson
                </a>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
                
                <?php if ($next_lesson): ?>
                <a href="/pages/learn.php?course=<?php echo $slug; ?>&lesson=<?php echo $next_lesson['id']; ?>" class="btn btn-primary">
                    Next Lesson<i class="fas fa-arrow-right ms-2"></i>
                </a>
                <?php elseif ($progress == 100): ?>
                <a href="/pages/certificates.php" class="btn btn-success">
                    <i class="fas fa-certificate me-2"></i>Get Certificate
                </a>
                <?php else: ?>
                <div></div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-play-circle fa-4x text-muted mb-3"></i>
                <h4>Select a lesson to start learning</h4>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
