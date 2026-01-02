<?php
require_once __DIR__ . '/config/init.php';

$db = new Database();
$conn = $db->getConnection();

$featured_courses = [];
$testimonials = [];
$categories = [];

if ($conn) {
    $stmt = $conn->query("SELECT c.*, cat.name as category_name, u.name as instructor_name 
                          FROM courses c 
                          LEFT JOIN categories cat ON c.category_id = cat.id 
                          LEFT JOIN users u ON c.instructor_id = u.id 
                          WHERE c.is_published = TRUE AND c.is_featured = TRUE 
                          ORDER BY c.created_at DESC LIMIT 6");
    $featured_courses = $stmt->fetchAll();

    $stmt = $conn->query("SELECT * FROM testimonials WHERE is_featured = TRUE ORDER BY created_at DESC LIMIT 3");
    $testimonials = $stmt->fetchAll();

    $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
}

$page_title = 'Home';
include 'includes/header.php';
?>

<section class="hero-section text-white">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Empowering Digital Skills for the Future</h1>
                <p class="lead mb-4">Join Mumtaz Digital Foundation and transform your career with industry-demanded digital skills. Learn from experts, earn certificates, and start freelancing.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="/pages/courses.php" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-book-open me-2"></i>View Courses
                    </a>
                    <a href="/pages/register.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-rocket me-2"></i>Get Started Free
                    </a>
                </div>
                <div class="mt-4 d-flex gap-4">
                    <div>
                        <h4 class="fw-bold mb-0">1000+</h4>
                        <small>Students</small>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">50+</h4>
                        <small>Courses</small>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-0">95%</h4>
                        <small>Success Rate</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="team-carousel-container" id="teamCarousel">
                    <div class="team-card-wrapper" data-index="0">
                        <img src="/attached_assets/stock_images/professional_busines_72adff50.jpg" alt="Team 1" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="1">
                        <img src="/attached_assets/stock_images/professional_busines_70034280.jpg" alt="Team 2" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="2">
                        <img src="/attached_assets/stock_images/professional_busines_1f60a9e9.jpg" alt="Team 3" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="3">
                        <img src="/attached_assets/stock_images/professional_busines_d00e8dcc.jpg" alt="Team 4" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="4">
                        <img src="/attached_assets/stock_images/professional_busines_d4ca588b.jpg" alt="Team 5" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="5">
                        <img src="/attached_assets/stock_images/professional_busines_0c4b7ef0.jpg" alt="Team 6" class="img-fluid rounded-4">
                    </div>
                    <div class="team-card-wrapper" data-index="6">
                        <img src="/attached_assets/stock_images/professional_busines_6457e788.jpg" alt="Team 7" class="img-fluid rounded-4">
                    </div>
                </div>
                <div class="team-carousel-controls">
                    <button class="carousel-btn carousel-btn-prev" id="prevTeam">Prev</button>
                    <button class="carousel-btn carousel-btn-next" id="nextTeam">Next</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Explore Categories</h2>
            <p class="section-subtitle">Choose from our top categories and start your learning journey</p>
        </div>
        <div class="row g-4">
            <?php foreach ($categories as $cat): ?>
            <div class="col-6 col-md-4 col-lg">
                <a href="/pages/courses.php?cat=<?php echo $cat['slug']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas <?php echo $cat['icon'] ?? 'fa-folder'; ?>"></i>
                    </div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($cat['name']); ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title mb-1">Featured Courses</h2>
                <p class="text-muted mb-0">Start your career with our best-selling courses</p>
            </div>
            <a href="/pages/courses.php" class="btn btn-outline-primary">View All Courses</a>
        </div>
        <div class="row g-4">
            <?php foreach ($featured_courses as $course): ?>
            <div class="col-md-6 col-lg-4">
                <div class="course-card position-relative">
                    <?php if ($course['is_featured']): ?>
                    <span class="course-badge badge-featured"><i class="fas fa-star me-1"></i>Featured</span>
                    <?php endif; ?>
                    <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=250&fit=crop" class="card-img-top" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="level-badge level-<?php echo $course['level']; ?>"><?php echo ucfirst($course['level']); ?></span>
                            <span class="text-muted small"><?php echo htmlspecialchars($course['category_name']); ?></span>
                        </div>
                        <h5 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr($course['short_description'], 0, 80)); ?>...</p>
                        <div class="course-meta">
                            <span><i class="fas fa-user me-1"></i><?php echo htmlspecialchars($course['instructor_name']); ?></span>
                            <span><i class="fas fa-clock me-1"></i><?php echo $course['duration'] ?? 'Self-paced'; ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="course-price">
                                <?php if ($course['price'] == 0): ?>
                                    <span class="text-success">Free</span>
                                <?php else: ?>
                                    <?php echo formatPrice($course['price']); ?>
                                    <?php if ($course['discount_price']): ?>
                                    <span class="original-price"><?php echo formatPrice($course['discount_price']); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <a href="/pages/course-detail.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary btn-sm">View Course</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Why Choose Mumtaz Digital Foundation?</h2>
            <p class="section-subtitle">We offer more than just courses - we build careers</p>
        </div>
        <div class="row g-4 marquee-container">
            <div class="marquee-content">
                <div class="marquee-track left-to-right">
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-laptop-code"></i></div>
                            <h5 class="fw-bold mb-3">Practical Learning</h5>
                            <p class="text-muted mb-0">Learn by doing with real-world projects, assignments, and hands-on exercises that prepare you for the industry.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                            <h5 class="fw-bold mb-3">Verified Certificates</h5>
                            <p class="text-muted mb-0">Earn industry-recognized certificates upon completion that boost your resume and LinkedIn profile.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-briefcase"></i></div>
                            <h5 class="fw-bold mb-3">Career Support</h5>
                            <p class="text-muted mb-0">Get freelancing guides, portfolio building, interview prep, and career counseling to kickstart your career.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-users"></i></div>
                            <h5 class="fw-bold mb-3">Expert Instructors</h5>
                            <p class="text-muted mb-0">Learn from industry professionals with years of real-world experience in their respective fields.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-infinity"></i></div>
                            <h5 class="fw-bold mb-3">Lifetime Access</h5>
                            <p class="text-muted mb-0">Get unlimited lifetime access to course materials, updates, and community support.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <h5 class="fw-bold mb-3">24/7 Support</h5>
                            <p class="text-muted mb-0">Get help whenever you need it through our community forums, Q&A, and dedicated support team.</p>
                        </div>
                    </div>
                    <!-- Duplicate for seamless loop -->
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-laptop-code"></i></div>
                            <h5 class="fw-bold mb-3">Practical Learning</h5>
                            <p class="text-muted mb-0">Learn by doing with real-world projects, assignments, and hands-on exercises that prepare you for the industry.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-certificate"></i></div>
                            <h5 class="fw-bold mb-3">Verified Certificates</h5>
                            <p class="text-muted mb-0">Earn industry-recognized certificates upon completion that boost your resume and LinkedIn profile.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-briefcase"></i></div>
                            <h5 class="fw-bold mb-3">Career Support</h5>
                            <p class="text-muted mb-0">Get freelancing guides, portfolio building, interview prep, and career counseling to kickstart your career.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-users"></i></div>
                            <h5 class="fw-bold mb-3">Expert Instructors</h5>
                            <p class="text-muted mb-0">Learn from industry professionals with years of real-world experience in their respective fields.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-infinity"></i></div>
                            <h5 class="fw-bold mb-3">Lifetime Access</h5>
                            <p class="text-muted mb-0">Get unlimited lifetime access to course materials, updates, and community support.</p>
                        </div>
                    </div>
                    <div class="feature-card-wrapper">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <h5 class="fw-bold mb-3">24/7 Support</h5>
                            <p class="text-muted mb-0">Get help whenever you need it through our community forums, Q&A, and dedicated support team.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="team-carousel-section">
    <div class="container text-center mb-5">
        <h2 class="section-title text-white">Our Expert Team</h2>
        <p class="section-subtitle">Meet the professionals behind Mumtaz Digital Foundation</p>
    </div>
    
    <div class="team-carousel-container" id="teamCarousel">
        <div class="team-card-wrapper" data-index="0">1</div>
        <div class="team-card-wrapper" data-index="1">2</div>
        <div class="team-card-wrapper" data-index="2">3</div>
        <div class="team-card-wrapper" data-index="3">4</div>
        <div class="team-card-wrapper" data-index="4">5</div>
        <div class="team-card-wrapper" data-index="5">6</div>
        <div class="team-card-wrapper" data-index="6">7</div>
    </div>

    <div class="team-carousel-controls">
        <button class="carousel-btn carousel-btn-prev" id="prevTeam">Prev</button>
        <button class="carousel-btn carousel-btn-next" id="nextTeam">Next</button>
    </div>
</section>

<section class="stats-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number">1000+</div>
                    <div class="stat-label">Happy Students</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Quality Courses</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Certificates Issued</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($testimonials)): ?>
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title"><span class="section-title-highlight">Success Stories</span></h2>
            <p class="section-subtitle">Hear from our successful students</p>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $testimonial): ?>
            <div class="col-md-4">
                <div class="testimonial-card h-100">
                    <div class="d-flex align-items-center mb-4">
                        <div class="testimonial-avatar-circle bg-primary text-white">
                            <?php echo strtoupper(substr($testimonial['name'], 0, 1)); ?>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($testimonial['name']); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($testimonial['role']); ?></small>
                        </div>
                    </div>
                    <p class="text-muted mb-4" style="line-height: 1.8;"><?php echo htmlspecialchars($testimonial['content']); ?></p>
                    <div class="rating-stars">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i < $testimonial['rating'] ? 'text-warning' : 'text-gray-300'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Your Learning Roadmap</h2>
            <p class="section-subtitle">Follow our structured path from beginner to professional</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row g-4">
                    <div class="col-md-3 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">1</div>
                        <h6 class="fw-bold">Choose a Course</h6>
                        <p class="text-muted small">Select a skill you want to master</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">2</div>
                        <h6 class="fw-bold">Learn & Practice</h6>
                        <p class="text-muted small">Watch lessons and complete assignments</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">3</div>
                        <h6 class="fw-bold">Get Certified</h6>
                        <p class="text-muted small">Pass the exam and earn your certificate</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.5rem;">4</div>
                        <h6 class="fw-bold">Start Earning</h6>
                        <p class="text-muted small">Apply skills in freelancing or job</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2 class="display-6 fw-bold mb-3">Ready to Transform Your Career?</h2>
        <p class="lead mb-4">Join thousands of students who have already started their journey with Mumtaz Digital Foundation</p>
        <a href="/pages/register.php" class="btn btn-light btn-lg px-5">
            <i class="fas fa-rocket me-2"></i>Start Learning Today
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
