# Welcome to DocStack

DocStack is a simple, flat-file documentation system built with PHP. No database required.

## Features

- **Flat-file storage** - Your content lives in markdown files
- **Zero configuration** - Works out of the box
- **Admin editing** - Built-in Monaco editor for content management
- **Customizable** - Branding, colors, and custom CSS/JS support
- **Dark mode** - Automatic theme detection
- **Responsive** - Works on desktop and mobile

## Getting Started

1. Copy `.env.example` to `.env`
2. Customize your settings
3. Add your content to the `content/` directory
4. Run with the PHP development server

```bash
php -S localhost:8000 -t public public/router.php
```

For production, use Apache or Nginx with the included `.htaccess` rules.

## Directory Structure

```
your-docs/
├── content/           # Your markdown files
│   ├── 01-section/    # Sections appear as tabs
│   │   ├── index.md   # Section landing page
│   │   └── 01-page.md # Pages in sidebar
│   └── 02-section/
├── public/
│   └── assets/
│       ├── custom.css # Your custom styles
│       └── custom.js  # Your custom scripts
└── .env               # Your configuration
```

## Learn More

- [Configuration Guide](getting-started/configuration)
- [Writing Content](getting-started/writing-content)
- [Customization](getting-started/customization)
