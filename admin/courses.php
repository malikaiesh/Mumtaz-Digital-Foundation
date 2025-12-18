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
    
    if ($action === 'add_course') {
        $title = sanitize($_POST['title'] ?? '');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
        $description = sanitize($_POST['description'] ?? '');
        $short_description = sanitize($_POST['short_description'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $level = sanitize($_POST['level'] ?? 'beginner');
        $duration = sanitize($_POST['duration'] ?? '');
        $outcomes = sanitize($_POST['outcomes'] ?? '');
        $skills = sanitize($_POST['skills'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO courses (title, slug, description, short_description, category_id, instructor_id, price, level, duration, outcomes, skills, is_featured, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$title, $slug, $description, $short_description, $category_id, $_SESSION['user_id'], $price, $level, $duration, $outcomes, $skills, $is_featured, $is_published])) {
            $success = 'Course added successfully!';
        } else {
            $error = 'Failed to add course.';
        }
    } elseif ($action === 'delete_course') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        if ($stmt->execute([$course_id])) {
            $success = 'Course deleted successfully!';
        }
    } elseif ($action === 'toggle_publish') {
        $course_id = (int)($_POST['course_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE courses SET is_published = NOT is_published WHERE id = ?");
        $stmt->execute([$course_id]);
        $success = 'Course status updated!';
    }
}

$stmt = $conn->query("SELECT c.*, cat.name as category_name FROM courses c LEFT JOIN categories cat ON c.category_id = cat.id ORDER BY c.created_at DESC");
$courses = $stmt->fetchAll();

$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$page_title = 'Manage Courses';
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
                <a href="/admin/courses.php" class="nav-link active"><i class="fas fa-book me-2"></i>Courses</a>
                <a href="/admin/students.php" class="nav-link"><i class="fas fa-users me-2"></i>Students</a>
                <a href="/admin/enrollments.php" class="nav-link"><i class="fas fa-user-graduate me-2"></i>Enrollments</a>
                <a href="/admin/payments.php" class="nav-link"><i class="fas fa-credit-card me-2"></i>Payments</a>
                <a href="/admin/categories.php" class="nav-link"><i class="fas fa-folder me-2"></i>Categories</a>
                <hr class="border-secondary my-2">
                <a href="/" class="nav-link"><i class="fas fa-home me-2"></i>View Website</a>
                <a href="/pages/logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
            </nav>
        </aside>
        
        <main class="admin-content">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Manage Courses</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fas fa-plus me-2"></i>Add Course
                    </button>
                </div>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Course</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Level</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                                            <?php if ($course['is_featured']): ?>
                                            <span class="badge bg-warning ms-1">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($course['category_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $course['price'] > 0 ? formatPrice($course['price']) : '<span class="text-success">Free</span>'; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo ucfirst($course['level']); ?></span></td>
                                        <td><?php echo $course['total_students']; ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle_publish">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-<?php echo $course['is_published'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $course['is_published'] ? 'Published' : 'Draft'; ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="/admin/course-edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this course?');">
                                                <input type="hidden" name="action" value="delete_course">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_course">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Course</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Course Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Short Description</label>
                                <input type="text" name="short_description" class="form-control" maxlength="500">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Full Description</label>
                                <textarea name="description" class="form-control" rows="4"></textarea>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (PKR)</label>
                                <input type="number" name="price" class="form-control" value="0" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Level</label>
                                <select name="level" class="form-select">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration</label>
                                <input type="text" name="duration" class="form-control" placeholder="e.g. 8 weeks">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Learning Outcomes (separate with |)</label>
                                <textarea name="outcomes" class="form-control" rows="2" placeholder="Outcome 1|Outcome 2|Outcome 3"></textarea>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Skills (separate with |)</label>
                                <input type="text" name="skills" class="form-control" placeholder="HTML|CSS|JavaScript">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                    <label class="form-check-label" for="is_featured">Featured Course</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" name="is_published" class="form-check-input" id="is_published">
                                    <label class="form-check-label" for="is_published">Publish Immediately</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
