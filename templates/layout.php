<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($branding['site_name']) ?></title>
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
                <a href="/docs" class="site-logo">
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
                    <a href="/docs/<?= htmlspecialchars($section['slug']) ?>"
                       class="header-tab<?= $currentSection === $section['slug'] ? ' active' : '' ?>">
                        <?= htmlspecialchars($section['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <?php if (\App\Config::feature('editing')): ?>
                <div class="admin-controls">
                    <span id="admin-badge" class="admin-badge" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Admin
                    </span>
                    <button id="admin-login-btn" class="admin-btn admin-btn-ghost admin-btn-sm" onclick="AdminEditor.showLoginModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                        Admin
                    </button>
                    <button id="admin-edit-btn" class="admin-btn admin-btn-ghost admin-btn-sm admin-only" onclick="AdminEditor.enterEditMode('<?= htmlspecialchars($currentPath ?? '') ?>')" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </button>
                    <button id="admin-logout-btn" class="admin-btn admin-btn-ghost admin-btn-sm" onclick="AdminEditor.logout()" style="display: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        Logout
                    </button>
                </div>
                <?php endif; ?>
                <?php if (!empty($branding['external_link_url'])): ?>
                <a href="<?= htmlspecialchars($branding['external_link_url']) ?>" class="header-external-link" target="_blank" rel="noopener noreferrer">
                    <?php if (!empty($branding['external_link_logo'])): ?>
                    <img src="<?= htmlspecialchars($branding['external_link_logo']) ?>" alt="<?= htmlspecialchars($branding['external_link_name']) ?>" width="16" height="16">
                    <?php endif; ?>
                    <?= htmlspecialchars($branding['external_link_name']) ?> &rarr;
                </a>
                <?php endif; ?>
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
                <?= $branding['footer_text'] ?>
                <div class="powered-by">
                    Powered by <a href="https://github.com/albrightlabs/docstack-core" target="_blank" rel="noopener">DocStack</a>
                </div>
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
                    <?= htmlspecialchars($heading['text']) ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <?php endif; ?>
    </div>

    <?php if (\App\Config::feature('editing')): ?>
    <?php include __DIR__ . '/admin-login.php'; ?>
    <?php endif; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
    <?php if (\App\Config::feature('editing')): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/9.1.6/marked.min.js"></script>
    <?php endif; ?>
    <script src="/assets/app.js"></script>
    <?php if (\App\Config::feature('editing')): ?>
    <script src="/assets/admin.js"></script>
    <script>
    // Pass admin state to JavaScript
    window.AdminState = {
        authenticated: <?= json_encode($isAdmin ?? false) ?>,
        csrfToken: <?= json_encode($csrfToken ?? null) ?>,
        currentPath: <?= json_encode($currentPath ?? '') ?>,
        editingEnabled: true
    };
    </script>
    <?php else: ?>
    <script>
    window.AdminState = { editingEnabled: false };
    </script>
    <?php endif; ?>
    <?php if (file_exists(__DIR__ . '/../public/assets/custom.js')): ?>
    <script src="/assets/custom.js"></script>
    <?php endif; ?>
    <script>
    function setFaviconFromEmoji(emoji, letter, options) {
        options = options || {};
        var size = options.size || 32;
        var letterFont = options.letterFont || 'bold 14px sans-serif';
        var fillStyle = options.fillStyle || 'white';
        var strokeStyle = options.strokeStyle || 'black';
        var padding = options.padding || 2;

        var canvas = document.createElement('canvas');
        canvas.width = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d');

        // Draw emoji as base
        ctx.font = (size - 4) + 'px serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(emoji, size / 2, size / 2 + 2);

        // Draw letter overlay if provided
        if (letter) {
            ctx.font = letterFont;
            ctx.textAlign = 'right';
            ctx.textBaseline = 'bottom';
            ctx.lineWidth = 2;
            var x = size - padding;
            var y = size - padding;
            ctx.strokeStyle = strokeStyle;
            ctx.strokeText(letter, x, y);
            ctx.fillStyle = fillStyle;
            ctx.fillText(letter, x, y);
        }

        // Set favicon
        var link = document.querySelector('link[rel="icon"]');
        if (!link) {
            link = document.createElement('link');
            link.rel = 'icon';
            document.head.appendChild(link);
        }
        link.type = 'image/png';
        link.href = canvas.toDataURL('image/png');
    }

    function setFaviconFromImage(imageUrl, letter, options) {
        options = options || {};
        var size = options.size || 32;
        var font = options.letterFont || 'bold 14px sans-serif';
        var fillStyle = options.fillStyle || 'white';
        var strokeStyle = options.strokeStyle || 'black';
        var padding = options.padding || 2;

        var img = new Image();
        img.crossOrigin = 'anonymous';

        img.onload = function() {
            var canvas = document.createElement('canvas');
            canvas.width = size;
            canvas.height = size;
            var ctx = canvas.getContext('2d');

            // Draw the base favicon
            ctx.drawImage(img, 0, 0, size, size);

            // Draw letter overlay if provided
            if (letter) {
                ctx.font = font;
                ctx.textAlign = 'right';
                ctx.textBaseline = 'bottom';
                ctx.lineWidth = 2;
                var x = size - padding;
                var y = size - padding;
                ctx.strokeStyle = strokeStyle;
                ctx.strokeText(letter, x, y);
                ctx.fillStyle = fillStyle;
                ctx.fillText(letter, x, y);
            }

            // Replace the favicon
            var link = document.querySelector('link[rel="icon"]');
            if (!link) {
                link = document.createElement('link');
                link.rel = 'icon';
                document.head.appendChild(link);
            }
            link.type = 'image/png';
            link.href = canvas.toDataURL('image/png');
        };

        img.src = imageUrl;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var faviconUrl = <?= json_encode($branding['favicon_url']) ?>;
        var faviconEmoji = <?= json_encode($branding['favicon_emoji']) ?>;
        var siteEmoji = <?= json_encode($branding['site_emoji']) ?>;
        var siteName = <?= json_encode($branding['site_name']) ?>;
        var customLetter = <?= json_encode($branding['favicon_letter']) ?>;
        var showLetter = <?= json_encode($branding['favicon_show_letter']) ?>;

        // Determine the letter to show (if any)
        var letter = null;
        if (showLetter) {
            letter = customLetter || siteName.charAt(0).toUpperCase();
        }

        var options = {
            letterFont: 'bold 16px sans-serif',
            padding: 1
        };

        // Determine favicon source: custom URL > custom emoji > site emoji
        if (faviconUrl) {
            setFaviconFromImage(faviconUrl, letter, options);
        } else {
            var emoji = faviconEmoji || siteEmoji || '📚';
            setFaviconFromEmoji(emoji, letter, options);
        }
    });
    </script>
</body>
</html>
