<?php
require_once __DIR__ . '/../../config/init.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$lesson_id = (int)($_POST['lesson_id'] ?? 0);
$action = sanitize($_POST['action'] ?? '');

if ($action !== 'mark_complete' || !$lesson_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT l.*, m.course_id FROM lessons l 
                        JOIN modules m ON l.module_id = m.id 
                        WHERE l.id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) {
    echo json_encode(['success' => false, 'error' => 'Lesson not found']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND payment_status = 'approved'");
$stmt->execute([$_SESSION['user_id'], $lesson['course_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Not enrolled']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO lesson_progress (user_id, lesson_id, is_completed, completed_at) 
                        VALUES (?, ?, TRUE, CURRENT_TIMESTAMP) 
                        ON CONFLICT (user_id, lesson_id) DO UPDATE SET is_completed = TRUE, completed_at = CURRENT_TIMESTAMP");
$stmt->execute([$_SESSION['user_id'], $lesson_id]);

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = ?");
$stmt->execute([$lesson['course_id']]);
$total = $stmt->fetch()['total'];

$stmt = $conn->prepare("SELECT COUNT(*) as completed FROM lesson_progress lp 
                        JOIN lessons l ON lp.lesson_id = l.id 
                        JOIN modules m ON l.module_id = m.id 
                        WHERE m.course_id = ? AND lp.user_id = ? AND lp.is_completed = TRUE");
$stmt->execute([$lesson['course_id'], $_SESSION['user_id']]);
$completed = $stmt->fetch()['completed'];

$progress = $total > 0 ? round(($completed / $total) * 100) : 0;

$stmt = $conn->prepare("UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?");
$stmt->execute([$progress, $_SESSION['user_id'], $lesson['course_id']]);

if ($progress == 100) {
    $stmt = $conn->prepare("UPDATE enrollments SET completed_at = CURRENT_TIMESTAMP WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $lesson['course_id']]);
}

echo json_encode(['success' => true, 'progress' => $progress]);
