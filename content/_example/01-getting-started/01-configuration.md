# Configuration

DocStack is configured entirely through environment variables in your `.env` file.

## Quick Setup

```bash
# Copy the example configuration
cp .env.example .env

# Edit with your settings
nano .env
```

## Configuration Options

### Site Identity

```env
SITE_NAME="My Documentation"
SITE_EMOJI="📚"
```

### Branding

```env
# Use a logo instead of emoji + text
LOGO_URL="/assets/logo.png"

# External link in header
EXTERNAL_LINK_NAME="Company Site"
EXTERNAL_LINK_URL="https://example.com"

# Footer
FOOTER_TEXT="© 2025 Your Company"
```

### Colors

```env
COLOR_PRIMARY="#3b82f6"
COLOR_PRIMARY_HOVER="#2563eb"
```

### Security

```env
# Password protect sections (add .protected file to section folder)
DOCS_PASSWORD="your-password"

# Enable admin editing
ADMIN_PASSWORD="admin-password"
```

### Features

```env
FEATURE_EDITING=true      # Admin editing
FEATURE_DARK_MODE=true    # Dark mode support
FEATURE_TOC=true          # Table of contents
```

## Custom Styling

For more extensive customization, create `public/assets/custom.css`:

```css
/* Override any default styles */
:root {
    --primary-color: #8b5cf6;
    --primary-hover: #7c3aed;
}

.site-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

## Custom JavaScript

Create `public/assets/custom.js` for custom behavior:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom JS loaded!');
});
```
