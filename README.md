# Docstack

A flat-file documentation system built with PHP by [Albright Labs](https://albrightlabs.com).

## Features

- **Tabbed navigation** - Top-level sections appear as header tabs
- **Hierarchical sidebar** - Nested sections with visual hierarchy
- **Dark/light mode** - Automatic based on system preference
- **Syntax highlighting** - Code blocks with proper theming for both modes
- **Internal linking** - Relative `.md` links work seamlessly
- **Table of contents** - Auto-generated from headings
- **Responsive design** - Works on desktop and mobile
- **Admin editing** - Built-in Monaco editor for content management
- **Environment configuration** - All settings via `.env` file

## Requirements

- PHP 8.1 or later
- Composer

## Installation

Docstack is designed as a core template that you clone and customize. Your content lives in the `content/` directory which is gitignored, allowing you to pull updates from the upstream repository without conflicts.

### Quick Start

```bash
# Clone the repository
git clone https://github.com/albrightlabs/docstack-core.git my-docs
cd my-docs

# Install dependencies
composer install

# Configure your instance
cp .env.example .env
# Edit .env with your settings (site name, colors, etc.)

# Add your content to the content/ directory
# See Content Structure below for organization

# Start the development server
php -S localhost:8000 -t public public/router.php
```

### Staying Updated

Since the `content/` directory is gitignored, you can pull updates from the upstream repository:

```bash
git pull origin main
composer install
```

Your content and customizations (in `.env`, `custom.css`, `custom.js`) remain untouched.

## Production

Point your web server's document root to the `public/` directory.

### Apache

Ensure `mod_rewrite` is enabled. The included `.htaccess` handles URL rewriting.

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Content Structure

Top-level directories become tabs in the header. Subdirectories become sections in the sidebar.

```
content/
├── 01-general/              # Tab 1: General
│   ├── index.md             # Tab landing page
│   ├── 01-getting-started/  # Sidebar section
│   │   ├── index.md
│   │   ├── 01-welcome.md
│   │   └── 02-workstation.md
│   └── 02-faq/
│       └── index.md
├── 02-developers/           # Tab 2: Developers
│   ├── index.md
│   ├── 01-setup/
│   └── 02-guides/
└── 03-company/              # Tab 3: Company
    ├── index.md
    ├── 01-about.md
    └── 02-team.md
```

## URL Mapping

| Content Path | URL |
|--------------|-----|
| `content/01-general/index.md` | `/docs/general` |
| `content/01-general/01-getting-started/index.md` | `/docs/general/getting-started` |
| `content/01-general/01-getting-started/01-welcome.md` | `/docs/general/getting-started/welcome` |

Numeric prefixes control ordering and are stripped from URLs and display names.

## Internal Links

Link between documents using relative paths:

```markdown
See the [Welcome Guide](./01-welcome.md)
See the [FAQ](../02-faq/index.md)
See the [Developer Docs](../../02-developers/index.md)
```

## Customization

All customization is done through your `.env` file and optional custom asset files.

### Branding (via .env)

```env
SITE_NAME="My Documentation"
SITE_EMOJI="📚"
LOGO_URL="/assets/logo.png"
FOOTER_TEXT="© 2025 Your Company"
```

### Colors (via .env)

```env
COLOR_PRIMARY="#3b82f6"
COLOR_PRIMARY_HOVER="#2563eb"
```

### Custom CSS

Create `public/assets/custom.css` for additional styling:

```css
:root {
    --primary-color: #8b5cf6;
}

.site-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

### Custom JavaScript

Create `public/assets/custom.js` for custom behavior.

### Dark Mode

Dark mode is automatic via `prefers-color-scheme` and can be toggled via:

```env
FEATURE_DARK_MODE=true
```

## Project Structure

```
docstack-core/
├── content/              # Your markdown documentation (gitignored)
│   └── _example/         # Example content (included in repo)
├── public/               # Web root
│   ├── index.php         # Front controller
│   ├── router.php        # Development server router
│   ├── .htaccess         # Apache URL rewriting
│   └── assets/
│       ├── style.css     # Core styles
│       ├── app.js        # Core JavaScript
│       ├── custom.css    # Your custom styles (gitignored)
│       └── custom.js     # Your custom scripts (gitignored)
├── src/                  # PHP application code
│   ├── Content.php       # Content/tree loading
│   ├── Markdown.php      # Markdown processing
│   ├── Config.php        # Configuration from .env
│   └── helpers.php       # Utilities
├── templates/            # PHP templates
│   ├── layout.php        # Main layout
│   ├── sidebar.php       # Sidebar rendering
│   ├── doc.php           # Document content
│   └── 404.php           # Not found page
├── .env                  # Your configuration (gitignored)
├── .env.example          # Example configuration
├── composer.json
└── README.md
```

## License

MIT

---

&copy; 2025 Albright Labs LLC.
