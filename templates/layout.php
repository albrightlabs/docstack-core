<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($branding['site_name']) ?></title>
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
    $showEditButton = true;
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
            <?php include __DIR__ . '/doc.php'; ?>
        </main>

        <?php if (!empty($headings) && \App\Config::feature('toc')): ?>
        <aside class="toc">
            <div class="toc-header">On This Page</div>
            <nav class="toc-nav">
                <?php foreach ($headings as $heading): ?>
                <a href="#<?= htmlspecialchars($heading['id']) ?>" class="toc-link toc-level-<?= $heading['level'] ?>">
                    <?= htmlspecialchars(stripEmojis($heading['text'])) ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <?php endif; ?>
    </div>

    <?php if (\App\Config::feature('editing') && \App\Auth::canEditContent()): ?>
    <?php include __DIR__ . '/admin-login.php'; ?>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <?php if (\App\Config::feature('editing') && \App\Auth::canEditContent()): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.6/purify.min.js"></script>
    <?php endif; ?>
    <script src="/assets/app.js"></script>
    <script src="/assets/search.js"></script>
    <?php if (\App\Config::feature('editing') && \App\Auth::canEditContent()): ?>
    <script src="/assets/admin.js"></script>
    <script>
    // Pass admin state to JavaScript
    window.AdminState = {
        authenticated: true,
        csrfToken: <?= json_encode($csrfToken ?? null) ?>,
        currentPath: <?= json_encode($currentPath ?? '') ?>,
        editingEnabled: true,
        basePath: <?= json_encode($basePath) ?>
    };
    </script>
    <?php else: ?>
    <script>
    window.AdminState = { authenticated: false, editingEnabled: false, basePath: <?= json_encode($basePath) ?> };
    </script>
    <?php endif; ?>
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

    <?php if (\App\Auth::check()): ?>
    <?php include __DIR__ . '/password-modal.php'; ?>
    <?php endif; ?>
</body>
</html>
