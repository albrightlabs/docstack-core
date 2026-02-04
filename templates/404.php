<?php
$basePath = \App\Config::getBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found | <?= htmlspecialchars($branding['site_name']) ?></title>
    <link rel="icon" type="image/png" href="<?= !empty($branding['favicon_url']) ? htmlspecialchars($branding['favicon_url']) : '/assets/favicon.png' ?>">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/assets/admin.css">
    <?php if (file_exists(__DIR__ . '/../public/assets/custom.css')): ?>
    <link rel="stylesheet" href="/assets/custom.css">
    <?php endif; ?>
    <?php if ($branding['color_primary'] !== '#3b82f6'): ?>
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($branding['color_primary']) ?>;
            --primary-hover: <?= htmlspecialchars($branding['color_primary_hover']) ?>;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php
    $showSidebar = true;
    include __DIR__ . '/header.php';
    ?>

    <div class="sidebar-overlay"></div>

    <div class="layout">
        <aside class="sidebar">
            <button class="sidebar-close" aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <nav class="sidebar-nav">
                <?php include __DIR__ . '/sidebar.php'; ?>
            </nav>
            <div class="sidebar-footer">
                <?php if (!empty($branding['footer_text'])): ?>
                <div class="footer-text"><?= $branding['footer_text'] ?></div>
                <?php endif; ?>
                <?php if ($branding['footer_show_powered_by']): ?>
                <div class="powered-by">
                    Powered by <a href="https://github.com/albrightlabs<?= $basePath ?: '/' ?>tack-core" target="_blank" rel="noopener">DocStack</a>
                </div>
                <?php endif; ?>
            </div>
        </aside>

        <main class="content">
            <article class="doc-content">
                <h1>Page Not Found</h1>
                <p>The documentation page you're looking for doesn't exist.</p>
                <p><a href="<?= $basePath ?: '/' ?>">Return to the documentation home</a></p>
            </article>
        </main>
    </div>

    <script src="/assets/app.js"></script>
    <?php if (file_exists(__DIR__ . '/../public/assets/custom.js')): ?>
    <script src="/assets/custom.js"></script>
    <?php endif; ?>
    <script src="/assets/favicon.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        FaviconGenerator.init({
            faviconUrl: <?= json_encode($branding['favicon_url']) ?>,
            faviconEmoji: <?= json_encode($branding['favicon_emoji']) ?>,
            siteEmoji: <?= json_encode($branding['site_emoji']) ?>,
            siteName: <?= json_encode($branding['site_name']) ?>,
            faviconLetter: <?= json_encode($branding['favicon_letter']) ?>,
            faviconShowLetter: <?= json_encode($branding['favicon_show_letter']) ?>
        });
    });
    </script>
</body>
</html>
