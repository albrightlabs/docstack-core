/**
 * Admin Editor Module
 * Handles file management and Monaco editor integration for admin users
 */
const AdminEditor = {
    isAuthenticated: false,
    csrfToken: null,
    editor: null,
    currentFile: null,
    originalContent: null,
    hasUnsavedChanges: false,
    previewVisible: true,
    apiBase: '/docs/api', // Base URL for API calls

    /**
     * Initialize admin functionality
     */
    init: function() {
        // Check initial auth state from server
        if (window.AdminState) {
            this.isAuthenticated = window.AdminState.authenticated;
            this.csrfToken = window.AdminState.csrfToken;
        }

        // Show admin-only elements if authenticated
        if (this.isAuthenticated) {
            document.querySelectorAll('.admin-only').forEach(function(el) {
                el.style.display = '';
            });
        }

        this.bindEvents();
    },

    /**
     * Bind event listeners
     */
    bindEvents: function() {
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's' && AdminEditor.editor) {
                e.preventDefault();
                AdminEditor.saveFile();
            }

            // Escape to close modals or exit edit mode
            if (e.key === 'Escape') {
                var modal = document.querySelector('.admin-modal.show');
                if (modal) {
                    modal.classList.remove('show');
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
     * Enter edit mode for current document
     */
    enterEditMode: function(path) {
        if (!this.isAuthenticated) {
            window.location.href = '/docs/login';
            return;
        }

        // Store current path
        this.currentFile = path || window.location.pathname.replace(/^\/docs\/?/, '');

        // Fetch file content
        fetch(this.apiBase + '/files/' + this.currentFile)
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
                        'Close' +
                    '</button>' +
                    '<button class="admin-btn admin-btn-primary admin-btn-sm" onclick="AdminEditor.saveFile()">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>' +
                        'Save' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<div class="editor-panes">' +
                '<div class="editor-pane editor-pane-editor">' +
                    '<div class="editor-format-toolbar">' +
                        '<div class="format-toolbar-group">' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'bold\')" title="Bold (Ctrl+B)">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'italic\')" title="Italic (Ctrl+I)">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'strikethrough\')" title="Strikethrough">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.3 4.9c-1.2-.9-2.8-1.4-4.6-1.4-3.1 0-5.3 1.6-5.3 4.2 0 1.3.5 2.3 1.5 3"/><path d="M4 12h16"/><path d="M17.6 14.5c0 2.8-2.3 4.5-5.5 4.5-2 0-3.8-.6-5-1.7"/></svg>' +
                            '</button>' +
                        '</div>' +
                        '<div class="format-toolbar-divider"></div>' +
                        '<div class="format-toolbar-group">' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'h1\')" title="Heading 1">H1</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'h2\')" title="Heading 2">H2</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'h3\')" title="Heading 3">H3</button>' +
                        '</div>' +
                        '<div class="format-toolbar-divider"></div>' +
                        '<div class="format-toolbar-group">' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'ul\')" title="Bullet List">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor"/><circle cx="4" cy="12" r="1.5" fill="currentColor"/><circle cx="4" cy="18" r="1.5" fill="currentColor"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'ol\')" title="Numbered List">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="20" y2="6"/><line x1="10" y1="12" x2="20" y2="12"/><line x1="10" y1="18" x2="20" y2="18"/><text x="3" y="7" font-size="6" fill="currentColor" stroke="none">1</text><text x="3" y="13" font-size="6" fill="currentColor" stroke="none">2</text><text x="3" y="19" font-size="6" fill="currentColor" stroke="none">3</text></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'task\')" title="Task List">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="6" height="6" rx="1"/><path d="M5 8l1.5 1.5L9 7"/><line x1="12" y1="8" x2="21" y2="8"/><rect x="3" y="13" width="6" height="6" rx="1"/><line x1="12" y1="16" x2="21" y2="16"/></svg>' +
                            '</button>' +
                        '</div>' +
                        '<div class="format-toolbar-divider"></div>' +
                        '<div class="format-toolbar-group">' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'link\')" title="Insert Link">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'image\')" title="Insert Image URL">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.triggerImageUpload()" title="Upload Image">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.triggerFileUpload()" title="Upload File">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'codeblock\')" title="Code Block">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 8 5 12 9 16"/><polyline points="15 8 19 12 15 16"/></svg>' +
                            '</button>' +
                        '</div>' +
                        '<div class="format-toolbar-divider"></div>' +
                        '<div class="format-toolbar-group">' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'quote\')" title="Blockquote">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'hr\')" title="Horizontal Rule">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/></svg>' +
                            '</button>' +
                            '<button class="format-btn" onclick="AdminEditor.insertFormat(\'table\')" title="Insert Table">' +
                                '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div id="monaco-editor"></div>' +
                    '<input type="file" id="image-upload-input" accept="image/*" style="display: none;" onchange="AdminEditor.handleImageUpload(this)">' +
                    '<input type="file" id="file-upload-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,.zip" style="display: none;" onchange="AdminEditor.handleFileUpload(this)">' +
                '</div>' +
                '<div class="editor-pane editor-pane-preview doc-content" id="editor-preview"></div>' +
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

            // Sync scroll between editor and preview
            self.setupScrollSync();

            // Initial preview
            self.updatePreview(content);

            // Start auto-save
            self.startAutoSave();
        });
    },

    scrollSyncEnabled: true,
    isScrolling: false,

    /**
     * Setup synchronized scrolling between editor and preview
     */
    setupScrollSync: function() {
        var self = this;
        var preview = document.getElementById('editor-preview');

        if (!this.editor || !preview) return;

        // Editor scroll -> Preview scroll
        this.editor.onDidScrollChange(function(e) {
            if (!self.scrollSyncEnabled || self.isScrolling) return;
            if (!self.previewVisible) return;

            self.isScrolling = true;

            var scrollTop = e.scrollTop;
            var scrollHeight = self.editor.getScrollHeight();
            var clientHeight = self.editor.getLayoutInfo().height;
            var maxScroll = scrollHeight - clientHeight;

            if (maxScroll > 0) {
                var scrollPercent = scrollTop / maxScroll;
                var previewMaxScroll = preview.scrollHeight - preview.clientHeight;
                preview.scrollTop = scrollPercent * previewMaxScroll;
            }

            setTimeout(function() { self.isScrolling = false; }, 50);
        });

        // Preview scroll -> Editor scroll
        preview.addEventListener('scroll', function() {
            if (!self.scrollSyncEnabled || self.isScrolling) return;
            if (!self.editor) return;

            self.isScrolling = true;

            var scrollTop = preview.scrollTop;
            var maxScroll = preview.scrollHeight - preview.clientHeight;

            if (maxScroll > 0) {
                var scrollPercent = scrollTop / maxScroll;
                var editorScrollHeight = self.editor.getScrollHeight();
                var editorClientHeight = self.editor.getLayoutInfo().height;
                var editorMaxScroll = editorScrollHeight - editorClientHeight;
                self.editor.setScrollTop(scrollPercent * editorMaxScroll);
            }

            setTimeout(function() { self.isScrolling = false; }, 50);
        });
    },

    autoSaveInterval: null,

    /**
     * Insert markdown formatting at cursor
     */
    insertFormat: function(type) {
        if (!this.editor) return;

        var selection = this.editor.getSelection();
        var selectedText = this.editor.getModel().getValueInRange(selection);
        var insertText = '';
        var cursorOffset = 0;

        switch (type) {
            case 'bold':
                insertText = '**' + (selectedText || 'bold text') + '**';
                if (!selectedText) cursorOffset = -2;
                break;
            case 'italic':
                insertText = '_' + (selectedText || 'italic text') + '_';
                if (!selectedText) cursorOffset = -1;
                break;
            case 'strikethrough':
                insertText = '~~' + (selectedText || 'strikethrough') + '~~';
                if (!selectedText) cursorOffset = -2;
                break;
            case 'h1':
                insertText = '# ' + (selectedText || 'Heading 1');
                break;
            case 'h2':
                insertText = '## ' + (selectedText || 'Heading 2');
                break;
            case 'h3':
                insertText = '### ' + (selectedText || 'Heading 3');
                break;
            case 'ul':
                insertText = '- ' + (selectedText || 'List item');
                break;
            case 'ol':
                insertText = '1. ' + (selectedText || 'List item');
                break;
            case 'task':
                insertText = '- [ ] ' + (selectedText || 'Task item');
                break;
            case 'link':
                insertText = '[' + (selectedText || 'link text') + '](url)';
                if (!selectedText) cursorOffset = -5;
                break;
            case 'image':
                insertText = '![' + (selectedText || 'alt text') + '](image-url)';
                if (!selectedText) cursorOffset = -11;
                break;
            case 'code':
                insertText = '`' + (selectedText || 'code') + '`';
                if (!selectedText) cursorOffset = -1;
                break;
            case 'codeblock':
                insertText = '```\n' + (selectedText || 'code here') + '\n```';
                if (!selectedText) cursorOffset = -4;
                break;
            case 'quote':
                insertText = '> ' + (selectedText || 'Quote');
                break;
            case 'hr':
                insertText = '\n---\n';
                break;
            case 'table':
                insertText = '| Header 1 | Header 2 | Header 3 |\n| --- | --- | --- |\n| Cell 1 | Cell 2 | Cell 3 |';
                break;
        }

        // Execute the edit
        this.editor.executeEdits('', [{
            range: selection,
            text: insertText,
            forceMoveMarkers: true
        }]);

        // Focus back on editor
        this.editor.focus();
    },

    /**
     * Trigger image upload file picker
     */
    triggerImageUpload: function() {
        var input = document.getElementById('image-upload-input');
        if (input) {
            input.click();
        }
    },

    /**
     * Handle image file upload
     */
    handleImageUpload: function(input) {
        var self = this;
        var file = input.files[0];

        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            this.showToast('Please select an image file', 'error');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            this.showToast('Image must be less than 5MB', 'error');
            return;
        }

        // Show uploading indicator
        this.showToast('Uploading image...', 'success');

        // Create FormData and upload
        var formData = new FormData();
        formData.append('image', file);
        formData.append('csrf_token', this.csrfToken);

        fetch(this.apiBase + '/upload', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Insert markdown image at cursor
                var imageMarkdown = '![' + (data.data.filename || 'image') + '](' + data.data.url + ')';
                self.editor.executeEdits('', [{
                    range: self.editor.getSelection(),
                    text: imageMarkdown,
                    forceMoveMarkers: true
                }]);
                self.editor.focus();
                self.showToast('Image uploaded successfully', 'success');
            } else {
                self.showToast(data.error || 'Failed to upload image', 'error');
            }
        })
        .catch(function(error) {
            self.showToast('Failed to upload image', 'error');
        });

        // Reset input so same file can be uploaded again
        input.value = '';
    },

    /**
     * Trigger file upload file picker
     */
    triggerFileUpload: function() {
        var input = document.getElementById('file-upload-input');
        if (input) {
            input.click();
        }
    },

    /**
     * Handle document file upload
     */
    handleFileUpload: function(input) {
        var self = this;
        var file = input.files[0];

        if (!file) return;

        // Validate file type
        var allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'text/csv',
            'application/zip'
        ];
        var allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip'];
        var ext = file.name.split('.').pop().toLowerCase();

        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(ext)) {
            this.showToast('Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, TXT, CSV, ZIP', 'error');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            this.showToast('File must be less than 10MB', 'error');
            return;
        }

        // Show uploading indicator
        this.showToast('Uploading file...', 'success');

        // Create FormData and upload
        var formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', this.csrfToken);

        fetch(this.apiBase + '/upload', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Insert markdown link at cursor - will be transformed to attachment block
                var fileMarkdown = '[' + (data.data.filename || file.name) + '](' + data.data.url + ')';
                self.editor.executeEdits('', [{
                    range: self.editor.getSelection(),
                    text: fileMarkdown,
                    forceMoveMarkers: true
                }]);
                self.editor.focus();
                self.showToast('File uploaded successfully', 'success');
            } else {
                self.showToast(data.error || 'Failed to upload file', 'error');
            }
        })
        .catch(function(error) {
            self.showToast('Failed to upload file', 'error');
        });

        // Reset input so same file can be uploaded again
        input.value = '';
    },

    /**
     * Start auto-save
     */
    startAutoSave: function() {
        var self = this;

        // Auto-save every 30 seconds if there are unsaved changes
        this.autoSaveInterval = setInterval(function() {
            if (self.hasUnsavedChanges && self.editor && self.currentFile) {
                self.saveFile(true); // silent save
            }
        }, 30000);
    },

    /**
     * Stop auto-save
     */
    stopAutoSave: function() {
        if (this.autoSaveInterval) {
            clearInterval(this.autoSaveInterval);
            this.autoSaveInterval = null;
        }
    },

    previewTimeout: null,

    /**
     * Update preview pane (debounced server-side rendering)
     */
    updatePreview: function(markdown) {
        var preview = document.getElementById('editor-preview');
        if (!preview || preview.classList.contains('hidden')) return;

        var self = this;

        // Debounce to avoid too many requests
        if (this.previewTimeout) {
            clearTimeout(this.previewTimeout);
        }

        this.previewTimeout = setTimeout(function() {
            fetch(self.apiBase + '/preview', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    markdown: markdown
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.html !== undefined) {
                    preview.innerHTML = data.data.html;

                    // Apply syntax highlighting to code blocks
                    if (typeof hljs !== 'undefined') {
                        preview.querySelectorAll('pre code').forEach(function(block) {
                            hljs.highlightElement(block);
                        });
                    }
                }
            })
            .catch(function(error) {
                console.error('Preview error:', error);
            });
        }, 300);
    },

    /**
     * Escape HTML entities
     */
    escapeHtml: function(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
     * @param {boolean} silent - If true, don't show toast notification (for auto-save)
     */
    saveFile: function(silent) {
        if (!this.editor || !this.currentFile) return;

        var content = this.editor.getValue();

        fetch(this.apiBase + '/files/' + this.currentFile, {
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
                if (!silent) {
                    AdminEditor.showToast('File saved successfully', 'success');
                }
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

        // Stop auto-save
        this.stopAutoSave();

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
        document.getElementById('admin-create-modal').classList.add('show');
        document.getElementById('create-name').focus();
    },

    /**
     * Close create modal
     */
    closeCreateModal: function() {
        document.getElementById('admin-create-modal').classList.remove('show');
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

        fetch(this.apiBase + '/files/' + path, {
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
        document.getElementById('admin-delete-modal').classList.add('show');
    },

    /**
     * Close delete modal
     */
    closeDeleteModal: function() {
        document.getElementById('admin-delete-modal').classList.remove('show');
    },

    /**
     * Confirm delete
     */
    confirmDelete: function() {
        var path = document.getElementById('delete-item-path').value;
        var errorEl = document.getElementById('admin-delete-error');

        fetch(this.apiBase + '/files/' + path, {
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
        document.getElementById('admin-rename-modal').classList.add('show');
        document.getElementById('rename-new-name').focus();
        document.getElementById('rename-new-name').select();
    },

    /**
     * Close rename modal
     */
    closeRenameModal: function() {
        document.getElementById('admin-rename-modal').classList.remove('show');
    },

    /**
     * Handle rename form submission
     */
    handleRename: function(e) {
        e.preventDefault();

        var oldPath = document.getElementById('rename-old-path').value;
        var newName = document.getElementById('rename-new-name').value;
        var errorEl = document.getElementById('admin-rename-error');

        fetch(this.apiBase + '/files/' + oldPath + '/move', {
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
    },

    /**
     * Show cleanup uploads modal with preview
     */
    showCleanupModal: function() {
        if (!this.isAuthenticated) {
            return;
        }

        var self = this;

        // First, do a dry run to get the preview
        fetch(this.apiBase + '/cleanup-uploads')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function(data) {
                if (data.success) {
                    self.displayCleanupModal(data.data);
                } else {
                    self.showToast(data.error || 'Failed to analyze uploads', 'error');
                }
            })
            .catch(function(error) {
                console.error('Cleanup error:', error);
                self.showToast('Failed to analyze uploads: ' + error.message, 'error');
            });
    },

    /**
     * Display cleanup modal with data
     */
    displayCleanupModal: function(data) {
        // Create modal if it doesn't exist
        var modal = document.getElementById('admin-cleanup-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'admin-cleanup-modal';
            modal.className = 'admin-modal';
            modal.innerHTML =
                '<div class="admin-modal-backdrop" onclick="AdminEditor.closeCleanupModal()"></div>' +
                '<div class="admin-modal-content">' +
                    '<div class="admin-modal-header">' +
                        '<h3>Clean Up Unused Uploads</h3>' +
                        '<button type="button" class="admin-modal-close" onclick="AdminEditor.closeCleanupModal()">' +
                            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                                '<line x1="18" y1="6" x2="6" y2="18"></line>' +
                                '<line x1="6" y1="6" x2="18" y2="18"></line>' +
                            '</svg>' +
                        '</button>' +
                    '</div>' +
                    '<div class="admin-modal-body" id="cleanup-modal-body"></div>' +
                '</div>';
            document.body.appendChild(modal);
        }

        var body = document.getElementById('cleanup-modal-body');

        if (data.orphaned_count === 0) {
            body.innerHTML =
                '<div class="cleanup-summary">' +
                    '<p><strong>No unused files found.</strong></p>' +
                    '<p class="admin-warning">All ' + data.total_files + ' uploaded files are referenced in your content.</p>' +
                '</div>' +
                '<div class="admin-modal-actions">' +
                    '<button type="button" class="admin-btn admin-btn-secondary" onclick="AdminEditor.closeCleanupModal()">Close</button>' +
                '</div>';
        } else {
            var fileList = data.orphaned_files.map(function(f) {
                return '<li>' + AdminEditor.escapeHtml(f) + '</li>';
            }).join('');

            body.innerHTML =
                '<div class="cleanup-summary">' +
                    '<p><strong>' + data.orphaned_count + ' unused file' + (data.orphaned_count !== 1 ? 's' : '') + ' found</strong></p>' +
                    '<p class="admin-warning">These files are in the uploads folder but not referenced in any content:</p>' +
                    '<ul class="cleanup-file-list">' + fileList + '</ul>' +
                    '<p class="cleanup-stats">' +
                        'Total uploads: ' + data.total_files + ' &bull; ' +
                        'Referenced: ' + data.referenced_files + ' &bull; ' +
                        'Orphaned: ' + data.orphaned_count +
                    '</p>' +
                '</div>' +
                '<div class="admin-modal-actions">' +
                    '<button type="button" class="admin-btn admin-btn-secondary" onclick="AdminEditor.closeCleanupModal()">Cancel</button>' +
                    '<button type="button" class="admin-btn admin-btn-danger" onclick="AdminEditor.executeCleanup()">Delete ' + data.orphaned_count + ' File' + (data.orphaned_count !== 1 ? 's' : '') + '</button>' +
                '</div>';
        }

        modal.classList.add('show');
    },

    /**
     * Close cleanup modal
     */
    closeCleanupModal: function() {
        var modal = document.getElementById('admin-cleanup-modal');
        if (modal) {
            modal.classList.remove('show');
        }
    },

    /**
     * Execute the cleanup (delete orphaned files)
     */
    executeCleanup: function() {
        var self = this;

        fetch(this.apiBase + '/cleanup-uploads', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: this.csrfToken,
                dry_run: false
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                self.closeCleanupModal();
                if (data.data.deleted_count > 0) {
                    self.showToast('Deleted ' + data.data.deleted_count + ' file(s), freed ' + data.data.freed_human, 'success');
                } else {
                    self.showToast('No files were deleted', 'success');
                }
            } else {
                self.showToast(data.error || 'Failed to clean up files', 'error');
            }
        })
        .catch(function(error) {
            self.showToast('Failed to clean up files', 'error');
        });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AdminEditor.init();
});
