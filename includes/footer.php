    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-graduation-cap <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= SITE_NAME ?>
                    </h5>
                    <p class="text-white-50 mb-0"><?= __('simple_lms') ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3"><?= __('quick_links') ?></h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?= SITE_URL ?>" class="text-white-50 text-decoration-none"><i class="fas fa-home <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('home') ?></a></li>
                        <li class="mb-2"><a href="<?= SITE_URL ?>/contact.php" class="text-white-50 text-decoration-none"><i class="fas fa-envelope <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('contact') ?></a></li>
                        <li class="mb-2"><a href="<?= SITE_URL ?>/about.php" class="text-white-50 text-decoration-none"><i class="fas fa-circle-info <?= getLanguageDirection() === 'rtl' ? 'ms-2' : 'me-2' ?>"></i><?= __('about') ?></a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h6 class="fw-bold mb-3"><?= __('connect') ?></h6>
                    <div class="d-flex gap-2 mb-3">
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-sm rounded-circle" style="width: 36px; height: 36px; padding: 0;">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                    <div class="mt-3">
                        <div class="text-white-50 small">
                            <i class="fas fa-calendar-day <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <span id="current-date"></span>
                        </div>
                        <div class="text-white-50 small mt-1">
                            <i class="fas fa-clock <?= getLanguageDirection() === 'rtl' ? 'ms-1' : 'me-1' ?>"></i>
                            <span id="current-time"></span>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-3 bg-white opacity-25">
            <div class="text-center">
                <p class="text-white-50 mb-0">
                    <small>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. <?= __('all_rights_reserved') ?></small>
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    
    <!--JS -->
    <script src="<?= SITE_URL ?>/assets/js/main.js" defer></script>
</body>
</html>
