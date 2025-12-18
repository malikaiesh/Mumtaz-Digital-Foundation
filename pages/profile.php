<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = sanitize($_POST['name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $skills = sanitize($_POST['skills'] ?? '');
        $portfolio_url = sanitize($_POST['portfolio_url'] ?? '');
        
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, skills = ?, portfolio_url = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        
        if ($stmt->execute([$name, $phone, $bio, $skills, $portfolio_url, $_SESSION['user_id']])) {
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully!';
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        } else {
            $error = 'Failed to update profile.';
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            
            if ($stmt->execute([$hashed, $_SESSION['user_id']])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
        }
    }
}

$page_title = 'Profile Settings';
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
                        <a href="/pages/dashboard.php" class="nav-link"><i class="fas fa-home"></i> Dashboard</a>
                        <a href="/pages/my-courses.php" class="nav-link"><i class="fas fa-book"></i> My Courses</a>
                        <a href="/pages/certificates.php" class="nav-link"><i class="fas fa-certificate"></i> Certificates</a>
                        <a href="/pages/profile.php" class="nav-link active"><i class="fas fa-user"></i> Profile</a>
                        <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </nav>
                </div>
            </div>
            
            <div class="col-lg-9">
                <h3 class="fw-bold mb-4">Profile Settings</h3>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Personal Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Portfolio URL</label>
                                    <input type="url" name="portfolio_url" class="form-control" value="<?php echo htmlspecialchars($user['portfolio_url'] ?? ''); ?>" placeholder="https://yourportfolio.com">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Bio</label>
                                    <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Skills (comma separated)</label>
                                    <input type="text" name="skills" class="form-control" value="<?php echo htmlspecialchars($user['skills'] ?? ''); ?>" placeholder="HTML, CSS, JavaScript, PHP">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
                
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Change Password</h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-control" required minlength="6">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
