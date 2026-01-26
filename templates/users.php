<?php
declare(strict_types=1);

use App\Auth;
use App\Config;

$branding = Config::getBranding();
$appName = $branding['site_name'];
$csrfToken = Auth::getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - <?= htmlspecialchars($appName) ?></title>
    <link rel="icon" type="image/png" href="<?= !empty($branding['favicon_url']) ? htmlspecialchars($branding['favicon_url']) : '/assets/favicon.png' ?>">
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="stylesheet" href="/assets/admin.css">
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8f9fa;
            --bg-tertiary: #f1f3f5;
            --text-primary: #1a1a1a;
            --text-muted: #666;
            --border-color: #e5e5e5;
            --accent-color: #0066cc;
            --space-2: 6px;
            --space-3: 8px;
            --space-4: 12px;
            --space-5: 16px;
            --space-6: 20px;
            --space-8: 32px;
            --radius-md: 6px;
            --radius-lg: 8px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-primary: #1a1a1a;
                --bg-secondary: #242424;
                --bg-tertiary: #2d2d2d;
                --text-primary: #f5f5f5;
                --text-muted: #a0a0a0;
                --border-color: #333;
            }
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .users-page-container {
            padding: var(--space-8) var(--space-5);
            max-width: 900px;
            margin: 0 auto;
        }

        .users-page {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
        }

        .users-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-6);
        }

        .users-header h1 {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .users-list {
            display: flex;
            flex-direction: column;
            gap: var(--space-3);
        }

        .user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: var(--space-4) var(--space-5);
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            transition: border-color 0.15s ease;
        }

        .user-card:hover {
            border-color: var(--accent-color);
        }

        .user-card-info {
            display: flex;
            align-items: center;
            gap: var(--space-4);
        }

        .user-card-email {
            font-weight: normal;
            color: var(--text-primary);
        }

        .user-card-meta {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            font-size: 13px;
            color: var(--text-muted);
        }

        .user-card-actions {
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .role-badge {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-weight: 500;
        }

        .role-admin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .role-editor {
            background: #dcfce7;
            color: #166534;
        }

        .role-readonly {
            background: #f3f4f6;
            color: #6b7280;
        }

        .super-admin-badge {
            font-size: 11px;
            padding: 2px 8px;
            background: #8b5cf6;
            color: white;
            border-radius: 9999px;
            font-weight: 500;
        }

        @media (prefers-color-scheme: dark) {
            .role-admin {
                background: #1e3a5f;
                color: #60a5fa;
            }

            .role-editor {
                background: #14532d;
                color: #86efac;
            }

            .role-readonly {
                background: #374151;
                color: #9ca3af;
            }
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .text-muted {
            color: var(--text-muted);
        }

        /* Modal styles - matches TodoStack */
        .modal-overlay {
            display: flex;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .modal {
            transform: translateY(20px);
            transition: transform 0.2s ease;
        }

        .modal-overlay.show .modal {
            transform: translateY(0);
        }

        .modal {
            background: var(--bg-primary);
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 400px;
            margin: 20px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            padding: 4px;
            cursor: pointer;
            color: var(--text-muted);
            border-radius: 4px;
            transition: background-color 0.15s, color 0.15s;
        }

        .modal-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .modal-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-input,
        .form-select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            background: var(--bg-primary);
            color: var(--text-primary);
            box-sizing: border-box;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
        }

        .form-help {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 20px;
        }

        .empty-state {
            padding: 12px 0;
            color: var(--text-muted);
        }

        @media (max-width: 600px) {
            .user-card {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-3);
            }

            .user-card-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid var(--border-color, #e5e7eb);
            border-top-color: var(--primary-color, #3b82f6);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            vertical-align: middle;
            margin-right: 8px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Inline error message */
        .inline-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        @media (prefers-color-scheme: dark) {
            .inline-error {
                background: #450a0a;
                border-color: #7f1d1d;
                color: #fca5a5;
            }
        }

        /* Error state for empty state */
        .empty-state.error-state {
            color: #dc2626;
        }

        @media (prefers-color-scheme: dark) {
            .empty-state.error-state {
                color: #fca5a5;
            }
        }

        /* Disabled button state */
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="header-content" style="flex-direction: row; align-items: center; justify-content: space-between; height: 56px; padding: 0 16px;">
            <a href="/docs" class="site-logo">
                <?php if (!empty($branding['logo_url'])): ?>
                <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="<?= htmlspecialchars($branding['site_name']) ?>" style="height: 32px; width: auto;">
                <?php else: ?>
                <span class="site-logo-emoji"><?= $branding['site_emoji'] ?></span>
                <span><?= htmlspecialchars($branding['site_name']) ?></span>
                <?php endif; ?>
            </a>
            <div class="user-menu" id="user-menu">
                <button type="button" class="user-menu-toggle" id="user-menu-toggle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </button>
                <div class="user-menu-dropdown" id="user-menu-dropdown">
                    <div class="user-menu-info">
                        <span class="user-menu-name"><?= htmlspecialchars($currentUser['name'] ?? '') ?></span>
                        <span class="user-menu-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></span>
                        <span class="user-menu-role role-<?= htmlspecialchars($currentUser['role'] ?? 'readonly') ?>"><?php
                            $role = $currentUser['role'] ?? 'readonly';
                            echo $role === 'admin' ? 'Admin' : ($role === 'editor' ? 'Editor' : 'Read-Only');
                        ?></span>
                    </div>
                    <a href="/docs/logout" class="user-menu-item user-menu-item-danger">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="users-page-container">
        <div class="users-page">
            <div class="users-header">
                <h1>User Management</h1>
                <button type="button" class="btn btn-primary" id="add-user-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add User
                </button>
            </div>

            <div class="users-list" id="users-list">
                <div class="empty-state">Loading users...</div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" id="user-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title" id="user-modal-title">Add User</h2>
                <button type="button" class="modal-close" data-close="user-modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="user-form">
                    <div class="form-group">
                        <label class="form-label" for="user-name">Name</label>
                        <input type="text" class="form-input" id="user-name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-email">Email</label>
                        <input type="email" class="form-input" id="user-email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-password">Password</label>
                        <input type="password" class="form-input" id="user-password" name="password" minlength="8">
                        <small class="form-help" id="password-help">Minimum 8 characters</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-password-confirm">Confirm Password</label>
                        <input type="password" class="form-input" id="user-password-confirm" name="password_confirm" minlength="8">
                        <small class="form-help text-danger" id="password-match-error" style="display: none;">Passwords do not match</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-role">Role</label>
                        <select class="form-select" id="user-role" name="role" required>
                            <option value="admin">Admin (Full Access + User Management)</option>
                            <option value="editor">Editor (Full Access)</option>
                            <option value="readonly">Read-Only (View Only)</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" data-close="user-modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="user-save">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="delete-user-modal">
        <div class="modal">
            <div class="modal-header">
                <h2 class="modal-title">Delete User</h2>
                <button type="button" class="modal-close" data-close="delete-user-modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="delete-user-name"></strong>?</p>
                <p class="text-muted" style="font-size: 14px;">This action cannot be undone.</p>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-close="delete-user-modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-user">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    window.CURRENT_USER_ID = <?= json_encode($currentUser['id'] ?? '') ?>;

    document.addEventListener('DOMContentLoaded', function() {
        var users = [];
        var usersList = document.getElementById('users-list');
        var userModal = document.getElementById('user-modal');
        var deleteModal = document.getElementById('delete-user-modal');
        var userForm = document.getElementById('user-form');
        var deleteUserId = null;

        // Load users
        function loadUsers() {
            usersList.innerHTML = '<div class="empty-state"><div class="loading-spinner"></div> Loading users...</div>';

            fetch('/docs/api/users', {
                headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    users = data.data;
                    renderUsers();
                } else {
                    usersList.innerHTML = '<div class="empty-state error-state">Failed to load users</div>';
                }
            })
            .catch(function(error) {
                usersList.innerHTML = '<div class="empty-state error-state">Failed to load users: ' + escapeHtml(error.message) + '</div>';
            });
        }

        function renderUsers() {
            if (!users.length) {
                usersList.innerHTML = '<div class="empty-state">No users found</div>';
                return;
            }

            usersList.innerHTML = users.map(function(user) {
                var canEdit = !user.is_super_admin;
                var canDelete = !user.is_super_admin && user.id !== window.CURRENT_USER_ID;

                return '<div class="user-card" data-user-id="' + user.id + '">' +
                    '<div class="user-card-info">' +
                        '<span class="user-card-name">' + escapeHtml(user.name || '') + '</span>' +
                        '<span class="user-card-email">' + escapeHtml(user.email) + '</span>' +
                        '<div class="user-card-meta">' +
                            '<span class="role-badge role-' + user.role + '">' + (user.role === 'admin' ? 'Admin' : (user.role === 'editor' ? 'Editor' : 'Read-Only')) + '</span>' +
                            (user.is_super_admin ? '<span class="super-admin-badge">Super Admin</span>' : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="user-card-actions">' +
                        (canEdit ? '<a href="/docs/users/' + user.id + '/edit" class="btn btn-secondary btn-sm">Edit</a>' : '') +
                        (canDelete ? '<button type="button" class="btn btn-danger btn-sm delete-user" data-id="' + user.id + '" data-name="' + escapeHtml(user.name || user.email) + '">Delete</button>' : '') +
                        (!canEdit && !canDelete ? '<span class="text-muted" style="font-size: 12px;">Protected</span>' : '') +
                    '</div>' +
                '</div>';
            }).join('');

            // Bind delete buttons
            usersList.querySelectorAll('.delete-user').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deleteUserId = this.dataset.id;
                    document.getElementById('delete-user-name').textContent = this.dataset.name;
                    deleteModal.classList.add('show');
                });
            });
        }

        // Add user button
        var addBtn = document.getElementById('add-user-btn');
        if (addBtn) {
            addBtn.addEventListener('click', function() {
                document.getElementById('user-name').value = '';
                document.getElementById('user-email').value = '';
                document.getElementById('user-password').value = '';
                document.getElementById('user-password-confirm').value = '';
                document.getElementById('password-match-error').style.display = 'none';
                document.getElementById('user-role').value = 'readonly';
                userModal.classList.add('show');
            });
        }

        // Form submit (create user only - edit is on separate page)
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var password = document.getElementById('user-password').value;
            var passwordConfirm = document.getElementById('user-password-confirm').value;
            var passwordMatchError = document.getElementById('password-match-error');

            // Validate password confirmation
            if (password !== passwordConfirm) {
                passwordMatchError.style.display = 'block';
                return;
            }
            passwordMatchError.style.display = 'none';

            var data = {
                name: document.getElementById('user-name').value,
                email: document.getElementById('user-email').value,
                password: password,
                role: document.getElementById('user-role').value,
                csrf_token: window.CSRF_TOKEN
            };

            var submitBtn = userForm.querySelector('button[type="submit"]');
            var originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            fetch('/docs/api/users', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                if (result.success) {
                    userModal.classList.remove('show');
                    loadUsers();
                } else {
                    showError(userForm, result.error || 'Failed to create user');
                }
            })
            .catch(function(error) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showError(userForm, 'Network error: ' + error.message);
            });
        });

        // Confirm delete
        var deleteBtn = document.getElementById('confirm-delete-user');
        deleteBtn.addEventListener('click', function() {
            if (!deleteUserId) return;

            var originalText = deleteBtn.textContent;
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Deleting...';

            fetch('/docs/api/users/' + deleteUserId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = originalText;
                if (result.success) {
                    deleteModal.classList.remove('show');
                    deleteUserId = null;
                    loadUsers();
                } else {
                    showError(deleteModal.querySelector('.modal-content'), result.error || 'Failed to delete user');
                }
            })
            .catch(function(error) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = originalText;
                showError(deleteModal.querySelector('.modal-content'), 'Network error: ' + error.message);
            });
        });

        // Close modals
        document.querySelectorAll('[data-close]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById(this.dataset.close).classList.remove('show');
            });
        });

        // Close on backdrop click
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        // Show error message inline
        function showError(container, message) {
            // Remove any existing error
            var existingError = container.querySelector('.inline-error');
            if (existingError) {
                existingError.remove();
            }

            var errorDiv = document.createElement('div');
            errorDiv.className = 'inline-error';
            errorDiv.textContent = message;

            // Insert at top of container or before buttons
            var actions = container.querySelector('.modal-actions, .form-actions');
            if (actions) {
                actions.parentNode.insertBefore(errorDiv, actions);
            } else {
                container.insertBefore(errorDiv, container.firstChild);
            }

            // Auto-remove after 5 seconds
            setTimeout(function() {
                if (errorDiv.parentNode) {
                    errorDiv.remove();
                }
            }, 5000);
        }

        // Initial load
        loadUsers();

        // User menu toggle
        var userMenuToggle = document.getElementById('user-menu-toggle');
        var userMenuDropdown = document.getElementById('user-menu-dropdown');

        if (userMenuToggle && userMenuDropdown) {
            userMenuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenuDropdown.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                if (!userMenuDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
                    userMenuDropdown.classList.remove('show');
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userMenuDropdown.classList.remove('show');
                }
            });
        }
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
