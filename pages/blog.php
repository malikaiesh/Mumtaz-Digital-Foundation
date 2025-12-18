<?php
require_once __DIR__ . '/../config/init.php';

$db = new Database();
$conn = $db->getConnection();

$posts = [];
if ($conn) {
    $stmt = $conn->query("SELECT p.*, u.name as author_name FROM blog_posts p LEFT JOIN users u ON p.author_id = u.id WHERE p.is_published = TRUE ORDER BY p.created_at DESC");
    $posts = $stmt->fetchAll();
}

$page_title = 'Blog';
include __DIR__ . '/../includes/header.php';
?>

<section class="breadcrumb-section">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Blog</li>
            </ol>
        </nav>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Latest from Our Blog</h2>
            <p class="text-muted">Tips, tutorials, and insights to help you grow</p>
        </div>
        
        <?php if (empty($posts)): ?>
        <div class="text-center py-5">
            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
            <h4>No blog posts yet</h4>
            <p class="text-muted">Check back soon for tips, tutorials, and insights!</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($posts as $post): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <img src="https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=400&h=250&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($post['category'] ?? 'General'); ?></span>
                        <h5 class="fw-bold"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'] ?? '', 0, 120)); ?>...</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($post['author_name']); ?>
                            </small>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></small>
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
