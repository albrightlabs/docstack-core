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
    <title>Edit User - <?= htmlspecialchars($appName) ?></title>
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
            --accent-hover: #0052a3;
            --success-color: #16a34a;
            --danger-color: #dc2626;
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

        .page-container {
            padding: var(--space-8) var(--space-5);
            max-width: 900px;
            margin: 0 auto;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: var(--space-5);
            transition: color 0.15s;
        }

        .back-link:hover {
            color: var(--accent-color);
        }

        .page-section {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            margin-bottom: var(--space-5);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: var(--space-5);
            padding-bottom: var(--space-4);
            border-bottom: 1px solid var(--border-color);
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-4);
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: var(--space-4);
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

        .form-input:disabled,
        .form-select:disabled {
            background: var(--bg-tertiary);
            cursor: not-allowed;
        }

        .form-help {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
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

        .btn-primary:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
        }

        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--border-color);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-3);
            margin-top: var(--space-5);
            padding-top: var(--space-4);
            border-top: 1px solid var(--border-color);
        }

        /* Permission tree styles */
        .permission-option {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) 0;
        }

        .permission-option.full-access {
            padding: var(--space-4);
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-4);
        }

        .permission-divider {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            margin: var(--space-4) 0;
            color: var(--text-muted);
            font-size: 13px;
        }

        .permission-divider::before,
        .permission-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .permission-tree {
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: var(--space-4);
            max-height: 400px;
            overflow-y: auto;
        }

        .permission-tree.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        .tree-item {
            padding: var(--space-2) 0;
        }

        .tree-item-content {
            display: flex;
            align-items: center;
            gap: var(--space-3);
        }

        .tree-item-children {
            margin-left: 24px;
            border-left: 1px solid var(--border-color);
            padding-left: var(--space-4);
        }

        .tree-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent-color);
        }

        .tree-checkbox:disabled {
            cursor: not-allowed;
        }

        .tree-label {
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .tree-type-badge {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            background: var(--bg-tertiary);
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .select-all-row {
            display: flex;
            align-items: center;
            gap: var(--space-3);
            padding: var(--space-3) var(--space-4);
            background: var(--bg-secondary);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-3);
        }

        .status-message {
            padding: var(--space-3) var(--space-4);
            border-radius: var(--radius-md);
            font-size: 14px;
            margin-bottom: var(--space-4);
        }

        .status-message.success {
            background: #dcfce7;
            color: #166534;
        }

        .status-message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (prefers-color-scheme: dark) {
            .status-message.success {
                background: #14532d;
                color: #86efac;
            }

            .status-message.error {
                background: #450a0a;
                color: #fca5a5;
            }
        }

        .super-admin-notice {
            padding: var(--space-4);
            background: #fef3c7;
            color: #92400e;
            border-radius: var(--radius-md);
            font-size: 14px;
        }

        @media (prefers-color-scheme: dark) {
            .super-admin-notice {
                background: #451a03;
                color: #fcd34d;
            }
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
                        <span class="user-menu-role role-<?= htmlspecialchars($currentUser['role'] ?? 'readonly') ?>"><?= ($currentUser['role'] ?? 'admin') === 'admin' ? 'Admin' : 'Read-Only' ?></span>
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

    <div class="page-container">
        <a href="/docs/users" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Back to Users
        </a>

        <div id="status-container"></div>

        <!-- User Details Section -->
        <div class="page-section">
            <div class="section-header">
                <h2 class="section-title">User Details</h2>
            </div>

            <?php if ($editUser['is_super_admin'] ?? false): ?>
            <div class="super-admin-notice">
                This is a super admin account. Some settings cannot be changed.
            </div>
            <?php endif; ?>

            <form id="user-details-form">
                <input type="hidden" id="user-id" value="<?= htmlspecialchars($editUser['id'] ?? '') ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="user-name">Name</label>
                        <input type="text" class="form-input" id="user-name" name="name"
                               value="<?= htmlspecialchars($editUser['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-email">Email</label>
                        <input type="email" class="form-input" id="user-email" name="email"
                               value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="user-password">New Password</label>
                        <input type="password" class="form-input" id="user-password" name="password" minlength="8">
                        <small class="form-help">Leave blank to keep current password</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user-password-confirm">Confirm Password</label>
                        <input type="password" class="form-input" id="user-password-confirm" name="password_confirm" minlength="8">
                        <small class="form-help text-danger" id="password-match-error" style="display: none; color: var(--danger-color);">Passwords do not match</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user-role">Role</label>
                    <select class="form-select" id="user-role" name="role" <?= ($editUser['is_super_admin'] ?? false) ? 'disabled' : '' ?>>
                        <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (Full Access)</option>
                        <option value="readonly" <?= ($editUser['role'] ?? '') === 'readonly' ? 'selected' : '' ?>>Read-Only (View Only)</option>
                    </select>
                    <?php if ($editUser['is_super_admin'] ?? false): ?>
                    <small class="form-help">Super admin role cannot be changed</small>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="save-details-btn">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- Content Access Section -->
        <div class="page-section">
            <div class="section-header">
                <h2 class="section-title">Content Access</h2>
            </div>

            <?php if ($editUser['is_super_admin'] ?? false): ?>
            <div class="super-admin-notice">
                Super admins have full access to all content. Permissions cannot be modified.
            </div>
            <?php else: ?>
            <form id="permissions-form">
                <div class="permission-option full-access">
                    <input type="checkbox" id="full-access" class="tree-checkbox">
                    <label for="full-access" class="tree-label"><strong>Full Access</strong> - Can access all content</label>
                </div>

                <div class="permission-divider">Or select specific sections</div>

                <div class="select-all-row">
                    <input type="checkbox" id="select-all" class="tree-checkbox">
                    <label for="select-all" class="tree-label">Select All</label>
                </div>

                <div class="permission-tree" id="permission-tree">
                    <div style="color: var(--text-muted); padding: var(--space-4);">Loading content tree...</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="save-permissions-btn">Save Permissions</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
    window.CSRF_TOKEN = <?= json_encode($csrfToken) ?>;
    window.USER_ID = <?= json_encode($editUser['id'] ?? '') ?>;
    window.IS_SUPER_ADMIN = <?= json_encode($editUser['is_super_admin'] ?? false) ?>;
    window.CURRENT_PERMISSIONS = <?= json_encode($editUser['permissions'] ?? ['full_access' => false, 'sections' => []]) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        var contentTree = [];
        var permissionTree = document.getElementById('permission-tree');
        var fullAccessCheckbox = document.getElementById('full-access');
        var selectAllCheckbox = document.getElementById('select-all');

        // Show status message
        function showStatus(message, type) {
            var container = document.getElementById('status-container');
            container.innerHTML = '<div class="status-message ' + type + '">' + escapeHtml(message) + '</div>';
            setTimeout(function() {
                container.innerHTML = '';
            }, 5000);
        }

        // Load content tree for permissions
        function loadContentTree() {
            if (window.IS_SUPER_ADMIN) return;

            fetch('/docs/api/content/tree', {
                headers: { 'X-CSRF-Token': window.CSRF_TOKEN }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    contentTree = data.data;
                    renderPermissionTree();
                    applyCurrentPermissions();
                } else {
                    permissionTree.innerHTML = '<div style="color: var(--danger-color);">Failed to load content</div>';
                }
            })
            .catch(function(err) {
                permissionTree.innerHTML = '<div style="color: var(--danger-color);">Failed to load content: ' + err.message + '</div>';
            });
        }

        // Render permission tree
        function renderPermissionTree() {
            permissionTree.innerHTML = renderTreeItems(contentTree, '');
            bindTreeEvents();
        }

        function renderTreeItems(items, parentPath) {
            var html = '';
            items.forEach(function(item) {
                var itemPath = parentPath ? parentPath + '/' + item.slug : item.slug;
                var hasChildren = item.children && item.children.length > 0;
                var itemType = item.type === 'section' ? 'Section' : (item.type === 'dir' ? 'Folder' : 'Page');

                html += '<div class="tree-item" data-path="' + escapeHtml(itemPath) + '">';
                html += '<div class="tree-item-content">';
                html += '<input type="checkbox" class="tree-checkbox" data-path="' + escapeHtml(itemPath) + '" id="perm-' + escapeHtml(itemPath.replace(/\//g, '-')) + '">';
                html += '<label for="perm-' + escapeHtml(itemPath.replace(/\//g, '-')) + '" class="tree-label">' + escapeHtml(item.name) + '</label>';
                html += '<span class="tree-type-badge">' + itemType + '</span>';
                html += '</div>';

                if (hasChildren) {
                    html += '<div class="tree-item-children">';
                    html += renderTreeItems(item.children, item.type === 'section' ? item.slug : itemPath);
                    html += '</div>';
                }

                html += '</div>';
            });
            return html;
        }

        // Bind tree checkbox events
        function bindTreeEvents() {
            permissionTree.querySelectorAll('.tree-checkbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var path = this.dataset.path;
                    var checked = this.checked;

                    // Check/uncheck all children
                    var parent = this.closest('.tree-item');
                    var children = parent.querySelectorAll('.tree-item-children .tree-checkbox');
                    children.forEach(function(child) {
                        child.checked = checked;
                        child.indeterminate = false;
                    });

                    // Update parent states
                    updateParentStates();
                    updateSelectAllState();
                });
            });
        }

        // Update parent checkbox states (indeterminate)
        function updateParentStates() {
            // Work from deepest level up
            var items = permissionTree.querySelectorAll('.tree-item');
            var itemArray = Array.from(items).reverse();

            itemArray.forEach(function(item) {
                var children = item.querySelector('.tree-item-children');
                if (!children) return;

                var childCheckboxes = children.querySelectorAll(':scope > .tree-item > .tree-item-content > .tree-checkbox');
                if (childCheckboxes.length === 0) return;

                var checkbox = item.querySelector(':scope > .tree-item-content > .tree-checkbox');
                var checkedCount = 0;
                var indeterminateCount = 0;

                childCheckboxes.forEach(function(cb) {
                    if (cb.checked) checkedCount++;
                    if (cb.indeterminate) indeterminateCount++;
                });

                if (checkedCount === childCheckboxes.length && indeterminateCount === 0) {
                    checkbox.checked = true;
                    checkbox.indeterminate = false;
                } else if (checkedCount === 0 && indeterminateCount === 0) {
                    checkbox.checked = false;
                    checkbox.indeterminate = false;
                } else {
                    checkbox.checked = false;
                    checkbox.indeterminate = true;
                }
            });
        }

        // Update select all state
        function updateSelectAllState() {
            var allCheckboxes = permissionTree.querySelectorAll('.tree-checkbox');
            var checkedCount = 0;
            allCheckboxes.forEach(function(cb) {
                if (cb.checked) checkedCount++;
            });

            if (checkedCount === allCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }

        // Apply current permissions to tree
        function applyCurrentPermissions() {
            var perms = window.CURRENT_PERMISSIONS;

            if (perms.full_access) {
                fullAccessCheckbox.checked = true;
                permissionTree.classList.add('disabled');
                selectAllCheckbox.disabled = true;
            } else {
                fullAccessCheckbox.checked = false;
                permissionTree.classList.remove('disabled');
                selectAllCheckbox.disabled = false;

                // Check items based on sections array
                perms.sections.forEach(function(section) {
                    var checkbox = permissionTree.querySelector('.tree-checkbox[data-path="' + section + '"]');
                    if (checkbox) {
                        checkbox.checked = true;
                        // Also check all children
                        var parent = checkbox.closest('.tree-item');
                        var children = parent.querySelectorAll('.tree-item-children .tree-checkbox');
                        children.forEach(function(child) {
                            child.checked = true;
                        });
                    }
                });

                updateParentStates();
                updateSelectAllState();
            }
        }

        // Full access toggle
        if (fullAccessCheckbox) {
            fullAccessCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    permissionTree.classList.add('disabled');
                    selectAllCheckbox.disabled = true;
                } else {
                    permissionTree.classList.remove('disabled');
                    selectAllCheckbox.disabled = false;
                }
            });
        }

        // Select all toggle
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                var checked = this.checked;
                permissionTree.querySelectorAll('.tree-checkbox').forEach(function(cb) {
                    cb.checked = checked;
                    cb.indeterminate = false;
                });
            });
        }

        // User details form submit
        document.getElementById('user-details-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var password = document.getElementById('user-password').value;
            var passwordConfirm = document.getElementById('user-password-confirm').value;
            var matchError = document.getElementById('password-match-error');

            if (password && password !== passwordConfirm) {
                matchError.style.display = 'block';
                return;
            }
            matchError.style.display = 'none';

            var data = {
                name: document.getElementById('user-name').value,
                email: document.getElementById('user-email').value,
                role: document.getElementById('user-role').value,
                csrf_token: window.CSRF_TOKEN
            };

            if (password) {
                data.password = password;
            }

            fetch('/docs/api/users/' + window.USER_ID, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                if (result.success) {
                    showStatus('User details saved successfully', 'success');
                    document.getElementById('user-password').value = '';
                    document.getElementById('user-password-confirm').value = '';
                } else {
                    showStatus(result.error || 'Failed to save user details', 'error');
                }
            })
            .catch(function(err) {
                showStatus('Error: ' + err.message, 'error');
            });
        });

        // Permissions form submit
        var permissionsForm = document.getElementById('permissions-form');
        if (permissionsForm) {
            permissionsForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var permissions = {
                    full_access: fullAccessCheckbox.checked,
                    sections: []
                };

                if (!permissions.full_access) {
                    // Collect checked paths (only top-level of checked subtrees)
                    var checkedPaths = [];
                    permissionTree.querySelectorAll('.tree-checkbox:checked').forEach(function(cb) {
                        checkedPaths.push(cb.dataset.path);
                    });

                    // Filter to only include paths where parent is not checked
                    // (we only need the highest level checked paths)
                    permissions.sections = checkedPaths.filter(function(path) {
                        var parts = path.split('/');
                        if (parts.length === 1) return true;

                        // Check if any parent path is in the list
                        for (var i = 1; i < parts.length; i++) {
                            var parentPath = parts.slice(0, i).join('/');
                            if (checkedPaths.indexOf(parentPath) !== -1) {
                                return false;
                            }
                        }
                        return true;
                    });
                }

                fetch('/docs/api/users/' + window.USER_ID + '/permissions', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        permissions: permissions,
                        csrf_token: window.CSRF_TOKEN
                    })
                })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.success) {
                        showStatus('Permissions saved successfully', 'success');
                        window.CURRENT_PERMISSIONS = permissions;
                    } else {
                        showStatus(result.error || 'Failed to save permissions', 'error');
                    }
                })
                .catch(function(err) {
                    showStatus('Error: ' + err.message, 'error');
                });
            });
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

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
        }

        // Initial load
        loadContentTree();
    });
    </script>
</body>
</html>
