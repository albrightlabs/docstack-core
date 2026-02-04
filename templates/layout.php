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
    <!-- Change Password Modal -->
    <div class="modal-overlay" id="password-modal">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h2 class="modal-title">Change Password</h2>
                <button type="button" class="btn btn-icon modal-close" onclick="closeChangePasswordModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-input" id="current-password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-input" id="new-password" required>
                    <span class="form-help">Minimum 8 characters</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-input" id="confirm-password" required>
                </div>
                <div id="password-error" class="form-error" style="display: none;"></div>
                <div id="password-success" class="form-success" style="display: none;"></div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeChangePasswordModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="savePassword()">Save</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    function showChangePasswordModal() {
        document.getElementById('current-password').value = '';
        document.getElementById('new-password').value = '';
        document.getElementById('confirm-password').value = '';
        document.getElementById('password-error').style.display = 'none';
        document.getElementById('password-success').style.display = 'none';
        // Show form elements, hide success state
        document.querySelectorAll('#password-modal .form-group').forEach(el => el.style.display = '');
        document.querySelector('#password-modal .modal-actions').style.display = '';
        document.getElementById('password-modal').classList.add('show');
        document.getElementById('current-password').focus();
    }

    function closeChangePasswordModal() {
        document.getElementById('password-modal').classList.remove('show');
    }

    async function savePassword() {
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;
        const errorDiv = document.getElementById('password-error');

        if (newPassword.length < 8) {
            errorDiv.textContent = 'New password must be at least 8 characters';
            errorDiv.style.display = 'block';
            return;
        }
        if (newPassword !== confirmPassword) {
            errorDiv.textContent = 'Passwords do not match';
            errorDiv.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('/api/auth/password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.AdminState?.csrfToken || ''
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    csrf_token: window.AdminState?.csrfToken || ''
                })
            });

            const data = await response.json();
            if (data.success) {
                // Hide form, show success message
                document.querySelectorAll('#password-modal .form-group').forEach(el => el.style.display = 'none');
                document.querySelector('#password-modal .modal-actions').style.display = 'none';
                errorDiv.style.display = 'none';
                const successDiv = document.getElementById('password-success');
                successDiv.textContent = 'Password changed successfully';
                successDiv.style.display = 'block';
                // Auto-close after 1.5 seconds
                setTimeout(() => closeChangePasswordModal(), 1500);
            } else {
                errorDiv.textContent = data.error || 'Failed to change password';
                errorDiv.style.display = 'block';
            }
        } catch (error) {
            errorDiv.textContent = 'Failed to change password';
            errorDiv.style.display = 'block';
        }
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('password-modal').classList.contains('show')) {
            closeChangePasswordModal();
        }
    });

    // Close modal on backdrop click
    document.getElementById('password-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeChangePasswordModal();
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
