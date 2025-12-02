<?php
$pageTitle = 'About Us';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Main about section with centered content -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="display-5 fw-bold mb-4"><?= __('about_us') ?></h1>
                <p class="lead mb-5"><?= __('about_subtitle') ?></p>
                
                <!-- Mission and vision cards in two columns -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-bullseye fa-3x text-primary"></i>
                                </div>
                                <h3 class="h4 mb-3"><?= __('our_mission') ?></h3>
                                <p class="text-muted mb-0"><?= __('mission_text') ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3">
                                    <i class="fas fa-eye fa-3x text-primary"></i>
                                </div>
                                <h3 class="h4 mb-3"><?= __('our_vision') ?></h3>
                                <p class="text-muted mb-0"><?= __('vision_text') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Why choose us section with feature list -->
                <div class="bg-light rounded p-5 mb-5">
                    <h2 class="h3 mb-4"><?= __('why_choose') ?></h2>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5><?= __('user_friendly') ?></h5>
                                    <p class="text-muted mb-0"><?= __('user_friendly_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5><?= __('comprehensive_tools') ?></h5>
                                    <p class="text-muted mb-0"><?= __('comprehensive_tools_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5><?= __('trusted_universities') ?></h5>
                                    <p class="text-muted mb-0"><?= __('trusted_universities_desc') ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <div>
                                    <h5><?= __('support_24_7') ?></h5>
                                    <p class="text-muted mb-0"><?= __('support_24_7_desc') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <h2 class="h3 mb-4"><?= __('ready_to_start') ?></h2>
                    <p class="lead mb-4"><?= __('join_educators') ?></p>
                    <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary btn-lg px-5"><?= __('get_started_today') ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
