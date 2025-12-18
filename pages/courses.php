<?php
require_once __DIR__ . '/../config/init.php';

$db = new Database();
$conn = $db->getConnection();

$category = sanitize($_GET['cat'] ?? '');
$level = sanitize($_GET['level'] ?? '');
$price_filter = sanitize($_GET['price'] ?? '');
$search = sanitize($_GET['q'] ?? '');

$query = "SELECT c.*, cat.name as category_name, cat.slug as category_slug, u.name as instructor_name 
          FROM courses c 
          LEFT JOIN categories cat ON c.category_id = cat.id 
          LEFT JOIN users u ON c.instructor_id = u.id 
          WHERE c.is_published = TRUE";
$params = [];

if ($category) {
    $query .= " AND cat.slug = ?";
    $params[] = $category;
}
if ($level) {
    $query .= " AND c.level = ?";
    $params[] = $level;
}
if ($price_filter === 'free') {
    $query .= " AND c.price = 0";
} elseif ($price_filter === 'paid') {
    $query .= " AND c.price > 0";
}
if ($search) {
    $query .= " AND (c.title ILIKE ? OR c.description ILIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY c.is_featured DESC, c.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$courses = $stmt->fetchAll();

$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

$page_title = 'Courses';
include __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Courses</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Filters</h5>
                        
                        <form method="GET" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" name="q" class="form-control" placeholder="Search courses..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="cat" class="form-select">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['slug']; ?>" <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Level</label>
                                <select name="level" class="form-select">
                                    <option value="">All Levels</option>
                                    <option value="beginner" <?php echo $level === 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                    <option value="intermediate" <?php echo $level === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                    <option value="advanced" <?php echo $level === 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Price</label>
                                <select name="price" class="form-select">
                                    <option value="">All Prices</option>
                                    <option value="free" <?php echo $price_filter === 'free' ? 'selected' : ''; ?>>Free</option>
                                    <option value="paid" <?php echo $price_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="/pages/courses.php" class="btn btn-outline-secondary w-100 mt-2">Clear All</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <?php echo count($courses); ?> Course<?php echo count($courses) !== 1 ? 's' : ''; ?> Found
                    </h4>
                </div>
                
                <div class="row g-4">
                    <?php if (empty($courses)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>No courses found</h5>
                            <p class="text-muted">Try adjusting your filters or search term</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="course-card position-relative h-100">
                            <?php if ($course['is_featured']): ?>
                            <span class="course-badge badge-featured"><i class="fas fa-star me-1"></i>Featured</span>
                            <?php elseif ($course['price'] == 0): ?>
                            <span class="course-badge badge-free">Free</span>
                            <?php endif; ?>
                            <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=400&h=250&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="level-badge level-<?php echo $course['level']; ?>"><?php echo ucfirst($course['level']); ?></span>
                                    <span class="text-muted small"><?php echo htmlspecialchars($course['category_name']); ?></span>
                                </div>
                                <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                                <p class="text-muted small mb-3 flex-grow-1"><?php echo htmlspecialchars(substr($course['short_description'] ?? '', 0, 80)); ?>...</p>
                                <div class="course-meta mb-2">
                                    <span><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($course['instructor_name']); ?></span>
                                    <span><i class="fas fa-play-circle me-1"></i><?php echo $course['total_lessons']; ?> Lessons</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="course-price">
                                        <?php if ($course['price'] == 0): ?>
                                            <span class="text-success fw-bold">Free</span>
                                        <?php else: ?>
                                            <?php echo formatPrice($course['price']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <a href="/pages/course-detail.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary btn-sm">View Course</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
