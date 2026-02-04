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
                       class="header-tab<?= $currentSection === $section['slug'] ? ' active' : '' ?>">
                        <?= htmlspecialchars($section['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </nav>
                <div class="header-right">
                    <?php if (\App\Config::feature('editing') && \App\Auth::canEditContent()): ?>
                    <div class="admin-controls">
                        <span id="admin-badge" class="admin-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Admin
                        </span>
                        <button id="admin-edit-btn" class="admin-btn admin-btn-ghost admin-btn-sm" onclick="AdminEditor.enterEditMode('<?= htmlspecialchars($currentPath ?? '') ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            Edit
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
                            <?php if (\App\Auth::canWrite()): ?>
                            <a href="#" class="user-menu-item" onclick="event.preventDefault(); AdminEditor.showCleanupModal();">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Clean Up Uploads
                            </a>
                            <?php endif; ?>
                            <a href="#" class="user-menu-item" onclick="event.preventDefault(); showChangePasswordModal();">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Change Password
                            </a>
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
                    <?php elseif (!\App\Auth::check()): ?>
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
        editingEnabled: true
    };
    </script>
    <?php else: ?>
    <script>
    window.AdminState = { authenticated: false, editingEnabled: false };
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
