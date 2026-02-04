<?php
/**
 * Shared password change modal
 * Include this partial on any page that needs password change functionality
 * Requires: $basePath variable to be set
 */
?>
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
        const basePath = window.AdminState?.basePath || window.BASE_PATH || '';
        const response = await fetch(basePath + '/api/auth/password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.AdminState?.csrfToken || window.CSRF_TOKEN || ''
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                csrf_token: window.AdminState?.csrfToken || window.CSRF_TOKEN || ''
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
