<?php
$basePath = \App\Config::getBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - <?= htmlspecialchars($branding['site_name']) ?></title>
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
    <header class="site-header">
        <div class="header-content">
            <div style="display: flex; align-items: center;">
                <button class="mobile-menu-btn" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="<?= $basePath ?: '/' ?>" class="site-logo">
                    <?php if (!empty($branding['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="<?= htmlspecialchars($branding['site_name']) ?>" style="height: 24px; width: auto;">
                    <?php else: ?>
                    <span class="site-logo-emoji"><?= $branding['site_emoji'] ?></span>
                    <span><?= htmlspecialchars($branding['site_name']) ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="header-nav">
                <nav class="header-tabs">
                    <?php foreach ($sections as $section): ?>
                    <a href="<?= $basePath ?>/<?= htmlspecialchars($section['slug']) ?>"
                       class="header-tab<?= ($currentSection ?? '') === $section['slug'] ? ' active' : '' ?>">
                        <?= htmlspecialchars($section['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <div class="header-right">
                    <?php if (!empty($branding['external_link_url'])): ?>
                    <a href="<?= htmlspecialchars($branding['external_link_url']) ?>" class="header-external-link" target="_blank" rel="noopener noreferrer">
                        <?php if (!empty($branding['external_link_logo'])): ?>
                        <img src="<?= htmlspecialchars($branding['external_link_logo']) ?>" alt="<?= htmlspecialchars($branding['external_link_name']) ?>" width="16" height="16">
                        <?php endif; ?>
                        <?= htmlspecialchars($branding['external_link_name']) ?> &rarr;
                    </a>
                    <?php endif; ?>
                    <?php if (\App\Auth::check()): ?>
                    <div class="user-menu" id="user-menu">
                        <button type="button" class="user-menu-toggle" id="user-menu-toggle">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="user-menu-dropdown" id="user-menu-dropdown">
                            <div class="user-menu-info">
                                <span class="user-menu-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></span>
                            </div>
                            <?php if (\App\Auth::isAdmin()): ?>
                            <a href="<?= $basePath ?>/users" class="user-menu-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                                Manage Users
                            </a>
                            <?php endif; ?>
                            <a href="<?= $basePath ?>/logout" class="user-menu-item user-menu-item-danger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Sign Out
                            </a>
                        </div>
                    </div>
                    <?php else: ?>
                    <a href="<?= $basePath ?>/login" class="admin-btn admin-btn-ghost admin-btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        Sign In
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

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
