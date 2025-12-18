<?php
require_once __DIR__ . '/../config/init.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('/pages/login.php');
}

$db = new Database();
$conn = $db->getConnection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_category') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? 'fa-folder');
        
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $slug, $description, $icon])) {
            $success = 'Category added successfully!';
        } else {
            $error = 'Failed to add category.';
        }
    } elseif ($action === 'delete_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$category_id])) {
            $success = 'Category deleted!';
        }
    }
}

$stmt = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM courses WHERE category_id = c.id) as course_count FROM categories c ORDER BY c.name");
$categories = $stmt->fetchAll();

$page_title = 'Manage Categories';
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
                <a href="/admin/students.php" class="nav-link"><i class="fas fa-users me-2"></i>Students</a>
                <a href="/admin/enrollments.php" class="nav-link"><i class="fas fa-user-graduate me-2"></i>Enrollments</a>
                <a href="/admin/payments.php" class="nav-link"><i class="fas fa-credit-card me-2"></i>Payments</a>
                <a href="/admin/categories.php" class="nav-link active"><i class="fas fa-folder me-2"></i>Categories</a>
                <hr class="border-secondary my-2">
                <a href="/" class="nav-link"><i class="fas fa-home me-2"></i>View Website</a>
                <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Manage Categories</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="fas fa-plus me-2"></i>Add Category
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="row g-4">
                    <?php foreach ($categories as $category): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="fas <?php echo $category['icon'] ?? 'fa-folder'; ?> fa-lg"></i>
                                </div>
                                <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h5>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></p>
                                <span class="badge bg-primary mb-3"><?php echo $category['course_count']; ?> Courses</span>
                                <div>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>
    
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_category">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (Font Awesome class)</label>
                            <input type="text" name="icon" class="form-control" value="fa-folder" placeholder="e.g. fa-code, fa-paint-brush">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
