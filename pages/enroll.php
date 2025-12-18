<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$slug = sanitize($_GET['course'] ?? '');

$stmt = $conn->prepare("SELECT * FROM courses WHERE slug = ? AND is_published = TRUE");
$stmt->execute([$slug]);
$course = $stmt->fetch();

if (!$course) {
    redirect('/pages/courses.php');
}

$stmt = $conn->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$_SESSION['user_id'], $course['id']]);
$existing_enrollment = $stmt->fetch();

if ($existing_enrollment && $existing_enrollment['payment_status'] === 'approved') {
    redirect('/pages/learn.php?course=' . $slug);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = sanitize($_POST['payment_method'] ?? '');
    $transaction_id = sanitize($_POST['transaction_id'] ?? '');
    
    if ($course['price'] == 0) {
        $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id, payment_status, amount_paid) VALUES (?, ?, 'approved', 0)
                               ON CONFLICT (user_id, course_id) DO UPDATE SET payment_status = 'approved'");
        $stmt->execute([$_SESSION['user_id'], $course['id']]);
        
        $conn->prepare("UPDATE courses SET total_students = total_students + 1 WHERE id = ?")
             ->execute([$course['id']]);
        
        $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'success')")
             ->execute([$_SESSION['user_id'], 'Course Enrolled!', 'You have successfully enrolled in ' . $course['title']]);
        
        redirect('/pages/learn.php?course=' . $slug);
    } else {
        if (empty($payment_method)) {
            $error = 'Please select a payment method.';
        } else {
            if ($existing_enrollment) {
                $stmt = $conn->prepare("UPDATE enrollments SET payment_method = ?, transaction_id = ?, payment_status = 'pending', amount_paid = ? WHERE id = ?");
                $stmt->execute([$payment_method, $transaction_id, $course['price'], $existing_enrollment['id']]);
                $enrollment_id = $existing_enrollment['id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO enrollments (user_id, course_id, payment_method, transaction_id, payment_status, amount_paid) VALUES (?, ?, ?, ?, 'pending', ?)");
                $stmt->execute([$_SESSION['user_id'], $course['id'], $payment_method, $transaction_id, $course['price']]);
                $enrollment_id = $conn->lastInsertId();
            }
            
            $stmt = $conn->prepare("INSERT INTO payments (user_id, enrollment_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $enrollment_id, $course['price'], $payment_method, $transaction_id]);
            
            $success = 'Payment submitted successfully! Your enrollment will be activated once the payment is verified by admin.';
        }
    }
}

$page_title = 'Enroll in ' . $course['title'];
include __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-5">
                        <h3 class="fw-bold mb-4">Enroll in Course</h3>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                        <a href="/pages/my-courses.php" class="btn btn-primary">Go to My Courses</a>
                        <?php else: ?>
                        
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=100&h=70&fit=crop" class="rounded me-3" alt="">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1"><?php echo htmlspecialchars($course['title']); ?></h5>
                                        <p class="text-muted mb-0"><?php echo $course['duration'] ?? 'Self-paced'; ?></p>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($course['price'] == 0): ?>
                                        <h4 class="text-success mb-0">Free</h4>
                                        <?php else: ?>
                                        <h4 class="mb-0"><?php echo formatPrice($course['price']); ?></h4>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($course['price'] == 0): ?>
                        <form method="POST">
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-check me-2"></i>Enroll Now - Free
                            </button>
                        </form>
                        <?php else: ?>
                        <form method="POST" id="payment-form">
                            <h5 class="fw-bold mb-3">Select Payment Method</h5>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="payment_method" value="jazzcash" id="jazzcash">
                                    <label class="btn btn-outline-primary w-100 py-3" for="jazzcash">
                                        <i class="fas fa-mobile-alt fa-2x mb-2 d-block"></i>
                                        JazzCash
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="payment_method" value="easypaisa" id="easypaisa">
                                    <label class="btn btn-outline-success w-100 py-3" for="easypaisa">
                                        <i class="fas fa-mobile-alt fa-2x mb-2 d-block"></i>
                                        EasyPaisa
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="radio" class="btn-check" name="payment_method" value="bank" id="bank">
                                    <label class="btn btn-outline-secondary w-100 py-3" for="bank">
                                        <i class="fas fa-university fa-2x mb-2 d-block"></i>
                                        Bank Transfer
                                    </label>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6 class="fw-bold">Payment Instructions:</h6>
                                <ul class="mb-0">
                                    <li><strong>JazzCash:</strong> Send to 0300-1234567</li>
                                    <li><strong>EasyPaisa:</strong> Send to 0300-1234567</li>
                                    <li><strong>Bank Transfer:</strong> Account: 1234567890, HBL Bank</li>
                                </ul>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Transaction ID / Reference Number</label>
                                <input type="text" name="transaction_id" class="form-control form-control-lg" placeholder="Enter your transaction ID">
                                <small class="text-muted">Enter the transaction ID from your payment receipt</small>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-lock me-2"></i>Submit Payment - <?php echo formatPrice($course['price']); ?>
                            </button>
                            
                            <p class="text-muted text-center mt-3 small">
                                <i class="fas fa-shield-alt me-1"></i>Your payment is secure. Enrollment will be activated after verification.
                            </p>
                        </form>
                        <?php endif; ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
