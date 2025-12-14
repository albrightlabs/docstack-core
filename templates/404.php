<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - <?= htmlspecialchars($branding['site_name']) ?></title>
    <link rel="icon" type="image/png" href="<?= !empty($branding['favicon_url']) ? htmlspecialchars($branding['favicon_url']) : '/assets/favicon.png' ?>">
    <link rel="stylesheet" href="/assets/style.css">
    <?php if (file_exists(__DIR__ . '/../public/assets/custom.css')): ?>
    <link rel="stylesheet" href="/assets/custom.css">
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
                       class="header-tab<?= ($currentSection ?? '') === $section['slug'] ? ' active' : '' ?>">
                        <?= htmlspecialchars($section['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
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
                <?php if ($branding['footer_show_powered_by']): ?>
                <div class="powered-by">
                    Powered by <a href="https://github.com/albrightlabs/docstack-core" target="_blank" rel="noopener">DocStack</a>
                </div>
                <?php endif; ?>
            </div>
        </aside>

        <main class="content">
            <article class="doc-content">
                <h1>Page Not Found</h1>
                <p>The documentation page you're looking for doesn't exist.</p>
                <p><a href="/docs">Return to the documentation home</a></p>
            </article>
        </main>
    </div>

    <script src="/assets/app.js"></script>
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
        ctx.font = (size - 4) + 'px serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(emoji, size / 2, size / 2 + 2);

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
            ctx.drawImage(img, 0, 0, size, size);

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

        var letter = null;
        if (showLetter) {
            letter = customLetter || siteName.charAt(0).toUpperCase();
        }

        var options = { letterFont: 'bold 16px sans-serif', padding: 1 };

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
