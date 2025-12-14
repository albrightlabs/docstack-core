# Customization

DocStack offers several ways to customize your documentation site.

## Branding via .env

The simplest customizations are done through environment variables:

```env
SITE_NAME="My Docs"
SITE_EMOJI="🚀"
LOGO_URL="/assets/my-logo.png"
COLOR_PRIMARY="#8b5cf6"
```

## Custom CSS

For more control, create `public/assets/custom.css`:

```css
/* Custom font */
body {
    font-family: 'Inter', sans-serif;
}

/* Custom header */
.site-header {
    background: #1a1a2e;
    border-bottom: 2px solid #e94560;
}

/* Custom sidebar */
.sidebar {
    background: #16213e;
}

/* Custom code blocks */
pre {
    background: #0f3460;
    border-radius: 8px;
}
```

## Custom JavaScript

Create `public/assets/custom.js` for custom behavior:

```javascript
// Add copy button to code blocks
document.querySelectorAll('pre code').forEach(function(block) {
    var button = document.createElement('button');
    button.className = 'copy-btn';
    button.textContent = 'Copy';
    button.onclick = function() {
        navigator.clipboard.writeText(block.textContent);
        button.textContent = 'Copied!';
        setTimeout(function() {
            button.textContent = 'Copy';
        }, 2000);
    };
    block.parentNode.insertBefore(button, block);
});
```

## Logo Guidelines

When using a custom logo:

- **Format**: PNG, SVG, or WebP recommended
- **Height**: 24-32px works best in the header
- **Background**: Transparent backgrounds work with dark mode
- **File size**: Keep under 50KB for fast loading

## Color Variables

DocStack uses CSS custom properties. Override in `custom.css`:

```css
:root {
    /* Primary brand color */
    --primary-color: #3b82f6;
    --primary-hover: #2563eb;

    /* Background colors */
    --bg-primary: #ffffff;
    --bg-secondary: #f8fafc;

    /* Text colors */
    --text-primary: #1e293b;
    --text-secondary: #64748b;

    /* Border color */
    --border-color: #e2e8f0;
}

/* Dark mode overrides */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #0f172a;
        --bg-secondary: #1e293b;
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --border-color: #334155;
    }
}
```

## Favicon

Replace `public/assets/favicon.png` with your own icon. DocStack automatically adds the first letter of your site name as an overlay.

To disable the letter overlay, add to `custom.js`:

```javascript
window.skipFaviconLetter = true;
```
