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
<body class="password-page">
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
