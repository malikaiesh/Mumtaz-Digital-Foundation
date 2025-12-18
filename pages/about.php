<?php
require_once __DIR__ . '/../config/init.php';

$page_title = 'About Us';
include __DIR__ . '/../includes/header.php';
?>

<section class="hero-section text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4">About Mumtaz Digital Foundation</h1>
                <p class="lead">Empowering individuals with digital skills for a brighter future</p>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Our Mission</h2>
                <p class="lead text-muted">To democratize digital education and empower every individual with the skills needed to thrive in the digital economy.</p>
                <p>Mumtaz Digital Foundation was established with a vision to bridge the digital skills gap in Pakistan. We believe that everyone deserves access to quality education that can transform their careers and lives.</p>
                <p>Our courses are designed by industry experts and are focused on practical, job-ready skills. From web development to digital marketing, we cover the most in-demand skills that employers and clients are looking for.</p>
            </div>
            <div class="col-lg-6">
                <img src="https://illustrations.popsy.co/amber/remote-work.svg" alt="Our Mission" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">What Sets Us Apart</h2>
            <p class="text-muted">We're not just another online learning platform</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Industry-Ready Curriculum</h5>
                    <p class="text-muted mb-0">Our courses are designed based on current industry demands and updated regularly to stay relevant.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Mentorship & Support</h5>
                    <p class="text-muted mb-0">Get personalized guidance from experienced instructors who are invested in your success.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card h-100">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Career Launch Support</h5>
                    <p class="text-muted mb-0">From portfolio building to freelancing guidance, we support you beyond just learning.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 order-lg-2">
                <h2 class="fw-bold mb-4">Our Vision</h2>
                <p class="lead text-muted">To become Pakistan's leading platform for digital skills education and career development.</p>
                <ul class="list-unstyled">
                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Train 10,000+ students by 2025</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Launch 50+ comprehensive courses</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Help 5,000+ students start freelancing</li>
                    <li class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Partner with 100+ companies for job placements</li>
                </ul>
            </div>
            <div class="col-lg-6 order-lg-1">
                <img src="https://illustrations.popsy.co/amber/success.svg" alt="Our Vision" class="img-fluid">
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2 class="display-6 fw-bold mb-3">Join Our Community</h2>
        <p class="lead mb-4">Be part of a growing community of digital learners and professionals</p>
        <a href="/pages/register.php" class="btn btn-light btn-lg px-5">Get Started Today</a>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
