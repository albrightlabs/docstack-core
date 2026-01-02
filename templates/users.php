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
    <?php if ($branding['color_primary'] !== '#3b82f6'): ?>
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($branding['color_primary']) ?>;
            --primary-hover: <?= htmlspecialchars($branding['color_primary_hover']) ?>;
        }
    </style>
    <?php endif; ?>
    <style>
        .users-page-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .users-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .users-list {
            background: var(--bg-primary, #fff);
            border: 1px solid var(--border-color, #e5e5e5);
            border-radius: 8px;
            overflow: hidden;
        }

        .user-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color, #e5e5e5);
        }

        .user-item:last-child {
            border-bottom: none;
        }

        .user-info {
            flex: 1;
        }

        .user-email {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .user-meta {
            font-size: 13px;
            color: var(--text-muted, #666);
        }

        .user-badges {
            display: flex;
            gap: 8px;
            margin-right: 16px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-admin {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .badge-readonly {
            background: #f3f4f6;
            color: #6b7280;
        }

        .badge-super {
            background: #fef3c7;
            color: #b45309;
        }

        .user-actions {
            display: flex;
            gap: 8px;
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
            background: var(--primary-color, #3b82f6);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover, #2563eb);
        }

        .btn-secondary {
            background: var(--bg-tertiary, #f1f3f5);
            color: var(--text-primary, #1a1a1a);
        }

        .btn-secondary:hover {
            background: var(--bg-hover, #e5e5e5);
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

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted, #666);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .back-link:hover {
            color: var(--text-primary, #1a1a1a);
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: var(--bg-primary, #fff);
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
            padding: 20px;
            border-bottom: 1px solid var(--border-color, #e5e5e5);
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
            color: var(--text-muted, #666);
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
            border: 1px solid var(--border-color, #e5e5e5);
            border-radius: 6px;
            font-size: 14px;
            background: var(--bg-primary, #fff);
            color: var(--text-primary, #1a1a1a);
            box-sizing: border-box;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--primary-color, #3b82f6);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-help {
            font-size: 12px;
            color: var(--text-muted, #666);
            margin-top: 4px;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }

        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--text-muted, #666);
        }

        @media (prefers-color-scheme: dark) {
            .badge-admin {
                background: #1e3a5f;
                color: #60a5fa;
            }

            .badge-readonly {
                background: #374151;
                color: #9ca3af;
            }

            .badge-super {
                background: #78350f;
                color: #fbbf24;
            }
        }
    </style>
</head>
<body>
    <div class="users-page-container">
        <a href="/docs" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Documentation
        </a>

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
                    <input type="hidden" id="user-id" name="id">

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
                        <label class="form-label" for="user-role">Role</label>
                        <select class="form-select" id="user-role" name="role" required>
                            <option value="admin">Admin (Full Access)</option>
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
                <p>Are you sure you want to delete <strong id="delete-user-email"></strong>?</p>
                <p style="color: var(--text-muted, #666); font-size: 14px;">This action cannot be undone.</p>
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

    (function() {
        var usersList = document.getElementById('users-list');
        var userModal = document.getElementById('user-modal');
        var deleteModal = document.getElementById('delete-user-modal');
        var userForm = document.getElementById('user-form');
        var deleteUserId = null;

        // Load users
        function loadUsers() {
            fetch('/api/users', {
                headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    renderUsers(data.data);
                }
            });
        }

        function renderUsers(users) {
            if (!users.length) {
                usersList.innerHTML = '<div class="empty-state">No users found</div>';
                return;
            }

            usersList.innerHTML = users.map(function(user) {
                var badges = [];
                if (user.is_super_admin) {
                    badges.push('<span class="badge badge-super">Super Admin</span>');
                }
                badges.push('<span class="badge badge-' + user.role + '">' + (user.role === 'admin' ? 'Admin' : 'Read-Only') + '</span>');

                var canEdit = !user.is_super_admin;
                var canDelete = !user.is_super_admin && user.id !== window.CURRENT_USER_ID;

                return '<div class="user-item" data-user-id="' + user.id + '">' +
                    '<div class="user-info">' +
                        '<div class="user-email">' + escapeHtml(user.email) + '</div>' +
                        '<div class="user-meta">Created: ' + formatDate(user.created_at) + '</div>' +
                    '</div>' +
                    '<div class="user-badges">' + badges.join('') + '</div>' +
                    '<div class="user-actions">' +
                        (canEdit ? '<button type="button" class="btn btn-secondary btn-sm edit-user" data-id="' + user.id + '">Edit</button>' : '') +
                        (canDelete ? '<button type="button" class="btn btn-danger btn-sm delete-user" data-id="' + user.id + '" data-email="' + escapeHtml(user.email) + '">Delete</button>' : '') +
                    '</div>' +
                '</div>';
            }).join('');

            // Bind edit buttons
            usersList.querySelectorAll('.edit-user').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    editUser(this.dataset.id);
                });
            });

            // Bind delete buttons
            usersList.querySelectorAll('.delete-user').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    deleteUserId = this.dataset.id;
                    document.getElementById('delete-user-email').textContent = this.dataset.email;
                    deleteModal.classList.add('active');
                });
            });
        }

        function editUser(id) {
            fetch('/api/users/' + id, {
                headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var user = data.data;
                    document.getElementById('user-modal-title').textContent = 'Edit User';
                    document.getElementById('user-id').value = user.id;
                    document.getElementById('user-email').value = user.email;
                    document.getElementById('user-password').value = '';
                    document.getElementById('user-password').required = false;
                    document.getElementById('password-help').textContent = 'Leave blank to keep current password';
                    document.getElementById('user-role').value = user.role;
                    document.getElementById('user-role').disabled = user.is_super_admin;
                    userModal.classList.add('active');
                }
            });
        }

        // Add user button
        document.getElementById('add-user-btn').addEventListener('click', function() {
            document.getElementById('user-modal-title').textContent = 'Add User';
            document.getElementById('user-id').value = '';
            document.getElementById('user-email').value = '';
            document.getElementById('user-password').value = '';
            document.getElementById('user-password').required = true;
            document.getElementById('password-help').textContent = 'Minimum 8 characters';
            document.getElementById('user-role').value = 'readonly';
            document.getElementById('user-role').disabled = false;
            userModal.classList.add('active');
        });

        // Form submit
        userForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var id = document.getElementById('user-id').value;
            var data = {
                email: document.getElementById('user-email').value,
                role: document.getElementById('user-role').value,
                csrf_token: window.CSRF_TOKEN
            };

            var password = document.getElementById('user-password').value;
            if (password) {
                data.password = password;
            }

            var url = id ? '/api/users/' + id : '/api/users';
            var method = id ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    userModal.classList.remove('active');
                    loadUsers();
                } else {
                    alert(result.error || 'Failed to save user');
                }
            });
        });

        // Confirm delete
        document.getElementById('confirm-delete-user').addEventListener('click', function() {
            if (!deleteUserId) return;

            fetch('/api/users/' + deleteUserId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    deleteModal.classList.remove('active');
                    deleteUserId = null;
                    loadUsers();
                } else {
                    alert(result.error || 'Failed to delete user');
                }
            });
        });

        // Close modals
        document.querySelectorAll('[data-close]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById(this.dataset.close).classList.remove('active');
            });
        });

        // Close on backdrop click
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            var d = new Date(dateStr);
            return d.toLocaleDateString();
        }

        // Initial load
        loadUsers();
    })();
    </script>
</body>
</html>
