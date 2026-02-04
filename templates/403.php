<?php
declare(strict_types=1);

use App\Auth;
use App\Config;

$branding = Config::getBranding();
$basePath = Config::getBasePath();
$isAuthenticated = Auth::check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - <?= htmlspecialchars($branding['site_name']) ?></title>
    <link rel="icon" type="image/png" href="<?= !empty($branding['favicon_url']) ? htmlspecialchars($branding['favicon_url']) : '/assets/favicon.png' ?>">
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 40px 20px;
            background: var(--bg-secondary, #f8f9fa);
        }

        .error-code {
            font-size: 120px;
            font-weight: 700;
            color: var(--text-muted, #666);
            line-height: 1;
            margin: 0;
            opacity: 0.3;
        }

        .error-title {
            font-size: 28px;
            font-weight: 600;
            margin: 20px 0 12px 0;
            color: var(--text-primary, #1a1a1a);
        }

        .error-message {
            font-size: 16px;
            color: var(--text-muted, #666);
            margin: 0 0 32px 0;
            max-width: 400px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
        }

        .error-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none !important;
            transition: all 0.15s;
        }

        .error-btn-primary {
            background: var(--accent-color, #0066cc);
            color: white !important;
        }

        .error-btn-primary:hover {
            background: var(--accent-hover, #0052a3);
            color: white !important;
        }

        .error-btn-secondary {
            background: var(--bg-tertiary, #e5e5e5);
            color: var(--text-primary, #1a1a1a);
        }

        .error-btn-secondary:hover {
            background: var(--border-color, #d0d0d0);
        }

        @media (prefers-color-scheme: dark) {
            .error-container {
                background: #1a1a1a;
            }

            .error-title {
                color: #f5f5f5;
            }

            .error-message {
                color: #a0a0a0;
            }

            .error-btn-secondary {
                background: #333;
                color: #f5f5f5;
            }

            .error-btn-secondary:hover {
                background: #444;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <p class="error-code">403</p>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            <?php if ($isAuthenticated): ?>
            You don't have permission to access this content.
            <?php else: ?>
            Please log in to access this content.
            <?php endif; ?>
        </p>
        <div class="error-actions">
            <a href="javascript:history.back()" class="error-btn error-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Go Back
            </a>
            <?php if ($isAuthenticated): ?>
            <a href="<?= $basePath ?: '/' ?>" class="error-btn error-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Go Home
            </a>
            <?php else: ?>
            <a href="<?= $basePath ?>/login" class="error-btn error-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                    <polyline points="10 17 15 12 10 7"></polyline>
                    <line x1="15" y1="12" x2="3" y2="12"></line>
                </svg>
                Log In
            </a>
            <?php endif; ?>
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
