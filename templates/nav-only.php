<?php
declare(strict_types=1);

use App\Config;

$branding = Config::getBranding();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigate to Content | <?= htmlspecialchars($branding['site_name']) ?></title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .nav-only-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60vh;
            text-align: center;
            padding: 40px 20px;
        }

        .nav-only-icon {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.6;
        }

        .nav-only-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 12px 0;
            color: var(--text-primary);
        }

        .nav-only-message {
            font-size: 16px;
            color: var(--text-muted);
            margin: 0 0 24px 0;
            max-width: 400px;
        }

        .nav-only-hint {
            font-size: 14px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-only-hint svg {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/layout.php'; ?>
</body>
</html>
