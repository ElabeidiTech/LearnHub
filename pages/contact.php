<?php
$pageTitle = 'Contact Us';
require_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-4"><?= __('contact_us') ?></h1>
                <p class="lead mb-5"><?= __('contact_subtitle') ?></p>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-4">
                        <div class="text-center p-4 bg-light rounded">
                            <i class="fas fa-envelope fa-2x text-primary mb-3"></i>
                            <h5><?= __('email') ?></h5>
                            <p class="text-muted mb-0">support@learnhub.com</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4 bg-light rounded">
                            <i class="fas fa-phone fa-2x text-primary mb-3"></i>
                            <h5><?= __('phone') ?></h5>
                            <p class="text-muted mb-0">+1 (555) 123-4567</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4 bg-light rounded">
                            <i class="fas fa-map-marker-alt fa-2x text-primary mb-3"></i>
                            <h5><?= __('address') ?></h5>
                            <p class="text-muted mb-0">Toronto, ON, Canada</p>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <form>
                            <div class="mb-3">
                                <label class="form-label"><?= __('name') ?></label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('email') ?></label>
                                <input type="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('subject') ?></label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= __('message') ?></label>
                                <textarea class="form-control" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><?= __('send_message') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
