/**
 * Admin Editor Module
 * Handles admin authentication, file management, and Monaco editor integration
 */
const AdminEditor = {
    isAuthenticated: false,
    csrfToken: null,
    editor: null,
    currentFile: null,
    originalContent: null,
    hasUnsavedChanges: false,
    previewVisible: true,

    /**
     * Initialize admin functionality
     */
    init: function() {
        // Check initial auth state from server
        if (window.AdminState) {
            this.isAuthenticated = window.AdminState.authenticated;
            this.csrfToken = window.AdminState.csrfToken;
        }

        this.updateUI();
        this.bindEvents();

        // Check auth status from API
        this.checkAuthStatus();
    },

    /**
     * Bind event listeners
     */
    bindEvents: function() {
        // Close modals on backdrop click
        document.querySelectorAll('.admin-modal-backdrop').forEach(function(backdrop) {
            backdrop.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && AdminEditor.editor) {
                e.preventDefault();
                AdminEditor.saveFile();
            }

            // Escape to close modals or exit edit mode
            if (e.key === 'Escape') {
                var modal = document.querySelector('.admin-modal[style*="display: flex"], .admin-modal:not([style*="display: none"])');
                if (modal && modal.style.display !== 'none') {
                    modal.style.display = 'none';
                }
            }
        });

        // Unsaved changes warning
        window.addEventListener('beforeunload', function(e) {
            if (AdminEditor.hasUnsavedChanges) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });
    },

    /**
     * Check authentication status from API
     */
    checkAuthStatus: function() {
        fetch('/api/auth/status')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                AdminEditor.isAuthenticated = data.authenticated;
                AdminEditor.csrfToken = data.csrf_token;
                AdminEditor.updateUI();
            })
            .catch(function(error) {
                console.error('Failed to check auth status:', error);
            });
    },

    /**
     * Update UI based on authentication state
     */
    updateUI: function() {
        var adminBtn = document.getElementById('admin-login-btn');
        var adminLogoutBtn = document.getElementById('admin-logout-btn');
        var adminBadge = document.getElementById('admin-badge');
        var adminControls = document.querySelectorAll('.admin-only');

        if (this.isAuthenticated) {
            if (adminBtn) adminBtn.style.display = 'none';
            if (adminLogoutBtn) adminLogoutBtn.style.display = 'inline-flex';
            if (adminBadge) adminBadge.style.display = 'inline-flex';
            adminControls.forEach(function(el) { el.style.display = ''; });
        } else {
            if (adminBtn) adminBtn.style.display = 'inline-flex';
            if (adminLogoutBtn) adminLogoutBtn.style.display = 'none';
            if (adminBadge) adminBadge.style.display = 'none';
            adminControls.forEach(function(el) { el.style.display = 'none'; });
        }
    },

    /**
     * Show login modal
     */
    showLoginModal: function() {
        var modal = document.getElementById('admin-login-modal');
        modal.style.display = 'flex';
        document.getElementById('admin-password').focus();
    },

    /**
     * Close login modal
     */
    closeLoginModal: function() {
        document.getElementById('admin-login-modal').style.display = 'none';
        document.getElementById('admin-password').value = '';
        document.getElementById('admin-login-error').style.display = 'none';
    },

    /**
     * Handle login form submission
     */
    handleLogin: function(e) {
        e.preventDefault();

        var password = document.getElementById('admin-password').value;
        var errorEl = document.getElementById('admin-login-error');

        fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: password })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                AdminEditor.isAuthenticated = true;
                AdminEditor.csrfToken = data.csrf_token;
                AdminEditor.closeLoginModal();
                AdminEditor.updateUI();
                AdminEditor.showToast('Logged in successfully', 'success');
            } else {
                errorEl.textContent = data.error || 'Invalid password';
                errorEl.style.display = 'block';
            }
        })
        .catch(function(error) {
            errorEl.textContent = 'Login failed. Please try again.';
            errorEl.style.display = 'block';
        });
    },

    /**
     * Handle logout
     */
    logout: function() {
        fetch('/api/auth/logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            AdminEditor.isAuthenticated = false;
            AdminEditor.csrfToken = null;
            AdminEditor.updateUI();
            AdminEditor.showToast('Logged out', 'success');

            // Exit edit mode if active
            if (AdminEditor.editor) {
                AdminEditor.exitEditMode();
            }
        });
    },

    /**
     * Enter edit mode for current document
     */
    enterEditMode: function(path) {
        if (!this.isAuthenticated) {
            this.showLoginModal();
            return;
        }

        // Store current path
        this.currentFile = path || window.location.pathname.replace(/^\/docs\/?/, '');

        // Fetch file content
        fetch('/api/files/' + this.currentFile)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    AdminEditor.showEditor(data.data);
                } else {
                    AdminEditor.showToast(data.error || 'Failed to load file', 'error');
                }
            })
            .catch(function(error) {
                AdminEditor.showToast('Failed to load file', 'error');
            });
    },

    /**
     * Show editor with file content
     */
    showEditor: function(fileData) {
        var content = document.querySelector('.content');
        var toc = document.querySelector('.toc');
        var layout = document.querySelector('.layout');

        // Hide TOC and expand content/layout
        if (toc) toc.style.display = 'none';
        content.classList.add('editing-mode');
        if (layout) layout.classList.add('editing-mode');

        // Store original content
        this.originalContent = fileData.content;

        // Create editor container
        content.innerHTML = '<div class="editor-container">' +
            '<div class="editor-toolbar">' +
                '<div class="editor-toolbar-left">' +
                    '<span class="editor-path">' + this.currentFile + '</span>' +
                    '<span class="editor-unsaved" id="editor-unsaved" style="display: none;">Unsaved changes</span>' +
                '</div>' +
                '<div class="editor-toolbar-right">' +
                    '<button class="admin-btn admin-btn-secondary admin-btn-sm" onclick="AdminEditor.togglePreview()">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>' +
                        'Preview' +
                    '</button>' +
                    '<button class="admin-btn admin-btn-secondary admin-btn-sm" onclick="AdminEditor.exitEditMode()">' +
                        'Cancel' +
                    '</button>' +
                    '<button class="admin-btn admin-btn-primary admin-btn-sm" onclick="AdminEditor.saveFile()">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>' +
                        'Save' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="editor-panes">' +
                '<div class="editor-pane editor-pane-editor">' +
                    '<div id="monaco-editor"></div>' +
                '</div>' +
                '<div class="editor-pane editor-pane-preview" id="editor-preview"></div>' +
            '</div>' +
        '</div>';

        // Initialize Monaco
        this.initMonaco(fileData.content);
    },

    /**
     * Initialize Monaco Editor
     */
    initMonaco: function(content) {
        var self = this;

        // Load Monaco from CDN
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }});

        require(['vs/editor/editor.main'], function() {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            self.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: content,
                language: 'markdown',
                theme: prefersDark ? 'vs-dark' : 'vs',
                automaticLayout: true,
                wordWrap: 'on',
                minimap: { enabled: true },
                lineNumbers: 'on',
                renderWhitespace: 'selection',
                fontSize: 14,
                fontFamily: '"SF Mono", Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
                scrollBeyondLastLine: false,
                padding: { top: 16 }
            });

            // Listen for changes
            self.editor.onDidChangeModelContent(function() {
                var currentContent = self.editor.getValue();
                self.hasUnsavedChanges = currentContent !== self.originalContent;
                document.getElementById('editor-unsaved').style.display = self.hasUnsavedChanges ? 'inline' : 'none';
                self.updatePreview(currentContent);
            });

            // Initial preview
            self.updatePreview(content);
        });
    },

    /**
     * Update preview pane
     */
    updatePreview: function(markdown) {
        var preview = document.getElementById('editor-preview');
        if (!preview || preview.classList.contains('hidden')) return;

        // Use marked.js if available, otherwise simple conversion
        if (typeof marked !== 'undefined') {
            preview.innerHTML = marked.parse(markdown);
        } else {
            // Simple markdown preview fallback
            var html = markdown
                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                .replace(/\*(.*)\*/gim, '<em>$1</em>')
                .replace(/\n/gim, '<br>');
            preview.innerHTML = html;
        }

        // Highlight code blocks
        preview.querySelectorAll('pre code').forEach(function(block) {
            if (typeof hljs !== 'undefined') {
                hljs.highlightElement(block);
            }
        });
    },

    /**
     * Toggle preview pane
     */
    togglePreview: function() {
        var preview = document.getElementById('editor-preview');
        if (preview) {
            preview.classList.toggle('hidden');
            this.previewVisible = !preview.classList.contains('hidden');
            if (this.previewVisible && this.editor) {
                this.updatePreview(this.editor.getValue());
            }
        }
    },

    /**
     * Save current file
     */
    saveFile: function() {
        if (!this.editor || !this.currentFile) return;

        var content = this.editor.getValue();

        fetch('/api/files/' + this.currentFile, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                content: content
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                AdminEditor.originalContent = content;
                AdminEditor.hasUnsavedChanges = false;
                document.getElementById('editor-unsaved').style.display = 'none';
                AdminEditor.showToast('File saved successfully', 'success');
            } else {
                AdminEditor.showToast(data.error || 'Failed to save file', 'error');
            }
        })
        .catch(function(error) {
            AdminEditor.showToast('Failed to save file', 'error');
        });
    },

    /**
     * Exit edit mode
     */
    exitEditMode: function() {
        if (this.hasUnsavedChanges) {
            if (!confirm('You have unsaved changes. Are you sure you want to exit?')) {
                return;
            }
        }

        this.editor = null;
        this.currentFile = null;
        this.originalContent = null;
        this.hasUnsavedChanges = false;

        // Reload the page to restore normal view
        window.location.reload();
    },

    /**
     * Show create modal
     */
    showCreateModal: function(parentPath) {
        document.getElementById('create-parent-path').value = parentPath || '';
        document.getElementById('create-name').value = '';
        document.getElementById('create-is-directory').checked = false;
        document.getElementById('admin-create-error').style.display = 'none';
        document.getElementById('admin-create-modal').style.display = 'flex';
        document.getElementById('create-name').focus();
    },

    /**
     * Close create modal
     */
    closeCreateModal: function() {
        document.getElementById('admin-create-modal').style.display = 'none';
    },

    /**
     * Handle create form submission
     */
    handleCreate: function(e) {
        e.preventDefault();

        var name = document.getElementById('create-name').value;
        var isDirectory = document.getElementById('create-is-directory').checked;
        var parentPath = document.getElementById('create-parent-path').value;
        var errorEl = document.getElementById('admin-create-error');

        var path = parentPath ? parentPath + '/' + name : name;

        fetch('/api/files/' + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                type: isDirectory ? 'directory' : 'file'
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                AdminEditor.closeCreateModal();
                AdminEditor.showToast('Created successfully', 'success');
                // Reload to show new item
                window.location.reload();
            } else {
                errorEl.textContent = data.error || 'Failed to create';
                errorEl.style.display = 'block';
            }
        })
        .catch(function(error) {
            errorEl.textContent = 'Failed to create. Please try again.';
            errorEl.style.display = 'block';
        });
    },

    /**
     * Show delete modal
     */
    showDeleteModal: function(path, name) {
        document.getElementById('delete-item-path').value = path;
        document.getElementById('delete-item-name').textContent = name;
        document.getElementById('admin-delete-error').style.display = 'none';
        document.getElementById('admin-delete-modal').style.display = 'flex';
    },

    /**
     * Close delete modal
     */
    closeDeleteModal: function() {
        document.getElementById('admin-delete-modal').style.display = 'none';
    },

    /**
     * Confirm delete
     */
    confirmDelete: function() {
        var path = document.getElementById('delete-item-path').value;
        var errorEl = document.getElementById('admin-delete-error');

        fetch('/api/files/' + path, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: this.csrfToken
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                AdminEditor.closeDeleteModal();
                AdminEditor.showToast('Deleted successfully', 'success');
                // Reload to update sidebar
                window.location.reload();
            } else {
                errorEl.textContent = data.error || 'Failed to delete';
                errorEl.style.display = 'block';
            }
        })
        .catch(function(error) {
            errorEl.textContent = 'Failed to delete. Please try again.';
            errorEl.style.display = 'block';
        });
    },

    /**
     * Show rename modal
     */
    showRenameModal: function(path, currentName) {
        document.getElementById('rename-old-path').value = path;
        document.getElementById('rename-new-name').value = currentName;
        document.getElementById('admin-rename-error').style.display = 'none';
        document.getElementById('admin-rename-modal').style.display = 'flex';
        document.getElementById('rename-new-name').focus();
        document.getElementById('rename-new-name').select();
    },

    /**
     * Close rename modal
     */
    closeRenameModal: function() {
        document.getElementById('admin-rename-modal').style.display = 'none';
    },

    /**
     * Handle rename form submission
     */
    handleRename: function(e) {
        e.preventDefault();

        var oldPath = document.getElementById('rename-old-path').value;
        var newName = document.getElementById('rename-new-name').value;
        var errorEl = document.getElementById('admin-rename-error');

        fetch('/api/files/' + oldPath + '/move', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                newFilename: newName
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                AdminEditor.closeRenameModal();
                AdminEditor.showToast('Renamed successfully', 'success');
                // Reload to update sidebar
                window.location.reload();
            } else {
                errorEl.textContent = data.error || 'Failed to rename';
                errorEl.style.display = 'block';
            }
        })
        .catch(function(error) {
            errorEl.textContent = 'Failed to rename. Please try again.';
            errorEl.style.display = 'block';
        });
    },

    /**
     * Show toast notification
     */
    showToast: function(message, type) {
        var toast = document.getElementById('admin-toast');
        toast.textContent = message;
        toast.className = 'admin-toast ' + (type || '');
        toast.style.display = 'block';

        setTimeout(function() {
            toast.style.display = 'none';
        }, 3000);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AdminEditor.init();
});
