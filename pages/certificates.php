<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT e.*, c.title, c.slug 
                        FROM enrollments e 
                        JOIN courses c ON e.course_id = c.id 
                        WHERE e.user_id = ? AND e.payment_status = 'approved' AND e.progress = 100");
$stmt->execute([$_SESSION['user_id']]);
$completed_courses = $stmt->fetchAll();

foreach ($completed_courses as $course) {
    $stmt = $conn->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$_SESSION['user_id'], $course['course_id']]);
    if (!$stmt->fetch()) {
        $certificate_id = generateCertificateId();
        $conn->prepare("INSERT INTO certificates (user_id, course_id, certificate_id) VALUES (?, ?, ?)")
             ->execute([$_SESSION['user_id'], $course['course_id'], $certificate_id]);
    }
}

$stmt = $conn->prepare("SELECT cert.*, c.title as course_title, u.name as user_name 
                        FROM certificates cert 
                        JOIN courses c ON cert.course_id = c.id 
                        JOIN users u ON cert.user_id = u.id 
                        WHERE cert.user_id = ? 
                        ORDER BY cert.issued_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$certificates = $stmt->fetchAll();

$page_title = 'My Certificates';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h3 class="fw-bold mb-4">My Certificates</h3>
        
        <?php if (empty($certificates)): ?>
        <div class="text-center py-5">
            <i class="fas fa-certificate fa-4x text-muted mb-3"></i>
            <h4>No certificates yet</h4>
            <p class="text-muted">Complete a course to earn your certificate</p>
            <a href="/pages/my-courses.php" class="btn btn-primary">Continue Learning</a>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($certificates as $cert): ?>
            <div class="col-md-6">
                <div class="certificate-card">
                    <div class="position-relative" style="z-index: 1;">
                        <i class="fas fa-award fa-3x text-warning mb-3"></i>
                        <h5 class="fw-bold mb-2">Certificate of Completion</h5>
                        <p class="mb-1">This is to certify that</p>
                        <h4 class="fw-bold mb-2" style="color: #ffd700;"><?php echo htmlspecialchars($cert['user_name']); ?></h4>
                        <p class="mb-1">has successfully completed</p>
                        <h5 class="mb-3"><?php echo htmlspecialchars($cert['course_title']); ?></h5>
                        <small class="d-block mb-3">Issued on <?php echo date('F d, Y', strtotime($cert['issued_at'])); ?></small>
                        <div class="d-flex gap-2 justify-content-center">
                            <span class="badge bg-light text-dark" onclick="copyCertificateId('<?php echo $cert['certificate_id']; ?>')" style="cursor: pointer;">
                                <i class="fas fa-fingerprint me-1"></i><?php echo $cert['certificate_id']; ?>
                            </span>
                        </div>
                        <div class="mt-3">
                            <a href="/pages/verify-certificate.php?id=<?php echo $cert['certificate_id']; ?>" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-check-circle me-1"></i>Verify
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
