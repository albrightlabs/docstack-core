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
    <title>Setup - <?= htmlspecialchars($appName) ?></title>
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
        <div class="password-icon">&#128075;</div>
        <h1>Welcome to <?= htmlspecialchars($appName) ?></h1>
        <p class="password-section-name">Create your administrator account to get started.</p>

        <?php if ($error): ?>
        <div class="password-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= $basePath ?>/setup" class="password-form" id="setup-form">
            <?= Auth::csrfField() ?>
            <input type="text" id="name" name="name" class="password-input"
                   placeholder="Your name"
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                   required autofocus>
            <input type="email" id="email" name="email" class="password-input"
                   placeholder="Email address"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                   required>
            <input type="password" id="password" name="password" class="password-input"
                   placeholder="Password (min 8 characters)" minlength="8" required>
            <input type="password" id="password_confirm" name="password_confirm" class="password-input"
                   placeholder="Confirm password" minlength="8" required>
            <div class="password-match-error" id="password-match-error" style="display: none; color: #ef4444; font-size: 14px; margin-bottom: 12px;">
                Passwords do not match
            </div>
            <button type="submit" class="password-submit">Complete Setup</button>
        </form>
    </div>

    <script>
    document.getElementById('setup-form').addEventListener('submit', function(e) {
        var password = document.getElementById('password').value;
        var confirm = document.getElementById('password_confirm').value;
        var errorEl = document.getElementById('password-match-error');

        if (password !== confirm) {
            e.preventDefault();
            errorEl.style.display = 'block';
            return false;
        }
        errorEl.style.display = 'none';
    });
    </script>

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
