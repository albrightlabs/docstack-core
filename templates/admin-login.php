<div id="admin-login-modal" class="admin-modal" style="display: none;">
    <div class="admin-modal-backdrop" onclick="AdminEditor.closeLoginModal()"></div>
    <div class="admin-login-container">
        <div class="password-icon">🔐</div>
        <h1>Admin Login</h1>
        <p class="admin-login-hint">Enter admin password to edit content.</p>

        <div id="admin-login-error" class="password-error" style="display: none;"></div>

        <form id="admin-login-form" class="password-form" onsubmit="AdminEditor.handleLogin(event)">
            <input
                type="password"
                id="admin-password"
                name="password"
                placeholder="Enter admin password"
                class="password-input"
                required
                autofocus
            >
            <button type="submit" class="password-submit">Unlock</button>
        </form>

        <button type="button" class="admin-login-cancel" onclick="AdminEditor.closeLoginModal()">Cancel</button>
    </div>
</div>

<div id="admin-create-modal" class="admin-modal" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3>Create New</h3>
            <button type="button" class="admin-modal-close" onclick="AdminEditor.closeCreateModal()">&times;</button>
        </div>
        <form id="admin-create-form" onsubmit="AdminEditor.handleCreate(event)">
            <div class="admin-form-group">
                <label for="create-name">Name</label>
                <input type="text" id="create-name" name="name" required placeholder="my-new-page">
            </div>
            <div class="admin-form-group">
                <label class="admin-checkbox-label">
                    <input type="checkbox" id="create-is-directory" name="is_directory">
                    Create as folder
                </label>
            </div>
            <input type="hidden" id="create-parent-path" name="parent_path" value="">
            <div id="admin-create-error" class="admin-error" style="display: none;"></div>
            <div class="admin-form-actions">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="AdminEditor.closeCreateModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<div id="admin-delete-modal" class="admin-modal" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3>Confirm Delete</h3>
            <button type="button" class="admin-modal-close" onclick="AdminEditor.closeDeleteModal()">&times;</button>
        </div>
        <div class="admin-modal-body">
            <p>Are you sure you want to delete <strong id="delete-item-name"></strong>?</p>
            <p class="admin-warning">This action cannot be undone. A backup will be created.</p>
        </div>
        <input type="hidden" id="delete-item-path" value="">
        <div id="admin-delete-error" class="admin-error" style="display: none;"></div>
        <div class="admin-form-actions">
            <button type="button" class="admin-btn admin-btn-secondary" onclick="AdminEditor.closeDeleteModal()">Cancel</button>
            <button type="button" class="admin-btn admin-btn-danger" onclick="AdminEditor.confirmDelete()">Delete</button>
        </div>
    </div>
</div>

<div id="admin-rename-modal" class="admin-modal" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3>Rename</h3>
            <button type="button" class="admin-modal-close" onclick="AdminEditor.closeRenameModal()">&times;</button>
        </div>
        <form id="admin-rename-form" onsubmit="AdminEditor.handleRename(event)">
            <div class="admin-form-group">
                <label for="rename-new-name">New Name</label>
                <input type="text" id="rename-new-name" name="new_name" required>
            </div>
            <input type="hidden" id="rename-old-path" name="old_path" value="">
            <div id="admin-rename-error" class="admin-error" style="display: none;"></div>
            <div class="admin-form-actions">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="AdminEditor.closeRenameModal()">Cancel</button>
                <button type="submit" class="admin-btn admin-btn-primary">Rename</button>
            </div>
        </form>
    </div>
</div>

<div id="admin-toast" class="admin-toast" style="display: none;"></div>
