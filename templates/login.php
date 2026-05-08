<?php
declare(strict_types=1);

use App\Auth;
use App\Config;

$branding = Config::getBranding();
$basePath = Config::getBasePath();
$appName = $branding['site_name'];
$error = $error ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?= htmlspecialchars($appName) ?></title>
    <link rel="icon" type="image/png" href="<?= !empty($branding['favicon_url']) ? htmlspecialchars($branding['favicon_url']) : '/assets/favicon.png' ?>">
    <link rel="stylesheet" href="/assets/style.css">
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
        <div>
            <div class="header-left">
                <span class="site-logo">
                    <?php if (!empty($branding['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="<?= htmlspecialchars($branding['site_name']) ?>"<?= !empty($branding['logo_width']) ? ' style="max-width: ' . htmlspecialchars($branding['logo_width']) . 'px;"' : '' ?>>
                    <?php else: ?>
                    <?php if (!empty($branding['site_emoji'])): ?>
                    <span class="site-logo-emoji"><?= htmlspecialchars($branding['site_emoji']) ?></span>
                    <?php endif; ?>
                    <?= htmlspecialchars($branding['site_name']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="header-right">
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

    <div class="password-page">
        <div class="password-container">
            <div class="password-icon">🔐</div>
            <h1>Sign In</h1>
            <p class="password-section-name">Enter your credentials to access <strong><?= htmlspecialchars($appName) ?></strong>.</p>

            <?php if ($error): ?>
            <div class="password-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= $basePath ?>/login" class="password-form">
                <?= Auth::csrfField() ?>
                <input type="email" id="email" name="email" class="password-input"
                       placeholder="Email address"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required autofocus>
                <input type="password" id="password" name="password" class="password-input"
                       placeholder="Password" required>
                <button type="submit" class="password-submit">Sign In</button>
            </form>
        </div>
    </div>

    <script src="/assets/favicon.js"></script>
    <script>
    FaviconGenerator.init({
        faviconUrl: <?= json_encode($branding['favicon_url']) ?>,
        faviconEmoji: <?= json_encode($branding['favicon_emoji']) ?>,
        siteEmoji: <?= json_encode($branding['site_emoji']) ?>,
        siteName: <?= json_encode($branding['site_name']) ?>,
        faviconLetter: <?= json_encode($branding['favicon_letter']) ?>,
        faviconShowLetter: <?= json_encode($branding['favicon_show_letter']) ?>
    });
    </script>
</body>
</html>
