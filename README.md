# Albright Wiki

A flat-file documentation system built with PHP for [albright.wiki](https://albright.wiki).

## Features

- **Tabbed navigation** - Top-level sections appear as header tabs
- **Hierarchical sidebar** - Nested sections with visual hierarchy
- **Dark/light mode** - Automatic based on system preference
- **Syntax highlighting** - Code blocks with proper theming for both modes
- **Internal linking** - Relative `.md` links work seamlessly
- **Table of contents** - Auto-generated from headings
- **Responsive design** - Works on desktop and mobile

## Requirements

- PHP 8.1 or later
- Composer

## Installation

```bash
composer install
```

## Development

Start the PHP built-in server:

```bash
php -S localhost:8000 -t public
```

Open [http://localhost:8000](http://localhost:8000) in your browser.

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

### Branding

- **Logo:** Replace `public/assets/logo.png`
- **Favicon:** Replace `public/assets/favicon.png`
- **Site name:** Update "Albright Wiki" in `templates/layout.php`

### Styling

Edit `public/assets/style.css`. The file uses CSS custom properties for theming:

```css
:root {
    --accent-color: #0066cc;
    --header-bg: #1a1a2e;
    /* ... */
}
```

### Dark Mode

Dark mode is automatic via `prefers-color-scheme`. Override variables in the `@media (prefers-color-scheme: dark)` block.

## Project Structure

```
md-explorer/
├── content/              # Markdown documentation
├── public/               # Web root
│   ├── index.php         # Front controller
│   ├── .htaccess         # Apache URL rewriting
│   └── assets/
│       ├── style.css     # Styles (with dark/light mode)
│       ├── app.js        # JavaScript
│       ├── logo.png      # Site logo
│       └── favicon.png   # Favicon
├── src/
│   ├── Content.php       # Content/tree loading
│   ├── Markdown.php      # Markdown processing
│   └── helpers.php       # Utilities
├── templates/
│   ├── layout.php        # Main layout
│   ├── sidebar.php       # Sidebar rendering
│   ├── doc.php           # Document content
│   └── 404.php           # Not found page
├── composer.json
└── README.md
```

## License

MIT

---

&copy; 2025 Albright Labs LLC. All Rights Reserved.
