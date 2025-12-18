<?php
require_once __DIR__ . '/../config/init.php';

$db = new Database();
$conn = $db->getConnection();

$certificate_id = sanitize($_GET['id'] ?? '');
$certificate = null;

if ($certificate_id) {
    $stmt = $conn->prepare("SELECT cert.*, c.title as course_title, u.name as user_name 
                            FROM certificates cert 
                            JOIN courses c ON cert.course_id = c.id 
                            JOIN users u ON cert.user_id = u.id 
                            WHERE cert.certificate_id = ?");
    $stmt->execute([$certificate_id]);
    $certificate = $stmt->fetch();
}

$page_title = 'Verify Certificate';
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-5 text-center">
                        <h3 class="fw-bold mb-4">Certificate Verification</h3>
                        
                        <form method="GET" class="mb-4">
                            <div class="input-group input-group-lg">
                                <input type="text" name="id" class="form-control" placeholder="Enter Certificate ID" value="<?php echo htmlspecialchars($certificate_id); ?>">
                                <button type="submit" class="btn btn-primary">Verify</button>
                            </div>
                        </form>
                        
                        <?php if ($certificate_id && $certificate): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <h4 class="fw-bold">Certificate Verified!</h4>
                            <p class="mb-0">This certificate is authentic and was issued by Mumtaz Digital Foundation.</p>
                        </div>
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted">Certificate ID:</td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($certificate['certificate_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Recipient:</td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($certificate['user_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Course:</td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($certificate['course_title']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Issue Date:</td>
                                        <td class="fw-bold"><?php echo date('F d, Y', strtotime($certificate['issued_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php elseif ($certificate_id): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle fa-3x mb-3"></i>
                            <h4 class="fw-bold">Certificate Not Found</h4>
                            <p class="mb-0">No certificate found with this ID. Please check the ID and try again.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
