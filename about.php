<?php
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-5 fw-bold mb-4">About LearnHub</h1>
                <p class="lead mb-5">Empowering education through innovative learning management solutions.</p>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-bullseye fa-3x text-primary"></i>
                                </div>
                                <h3 class="h4 mb-3">Our Mission</h3>
                                <p class="text-muted mb-0">To provide educational institutions with powerful, easy-to-use tools that enhance the teaching and learning experience for everyone.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-eye fa-3x text-primary"></i>
                                </div>
                                <h3 class="h4 mb-3">Our Vision</h3>
                                <p class="text-muted mb-0">To be the leading learning management platform that transforms education through technology, making quality education accessible to all.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-light rounded p-5 mb-5">
                    <h2 class="h3 mb-4">Why Choose LearnHub?</h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5>User-Friendly Interface</h5>
                                    <p class="text-muted mb-0">Intuitive design that makes it easy for teachers and students to navigate.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5>Comprehensive Tools</h5>
                                    <p class="text-muted mb-0">Everything you need for assignments, quizzes, grades, and more.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5>Trusted by Universities</h5>
                                    <p class="text-muted mb-0">Used by leading Canadian educational institutions.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5>24/7 Support</h5>
                                    <p class="text-muted mb-0">Our dedicated team is always here to help you succeed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <h2 class="h3 mb-4">Ready to Get Started?</h2>
                    <p class="lead text-muted mb-4">Join thousands of educators and students already using LearnHub.</p>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary btn-lg px-5">Get Started Today</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
