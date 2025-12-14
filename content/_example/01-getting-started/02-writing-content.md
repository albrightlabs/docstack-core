# Writing Content

DocStack uses standard Markdown with some conventions for organization.

## File Structure

### Sections (Tabs)

Top-level directories become tabs in the header:

```
content/
├── 01-guides/      → "Guides" tab
├── 02-api/         → "Api" tab
└── 03-examples/    → "Examples" tab
```

### Pages

Markdown files within sections appear in the sidebar:

```
content/01-guides/
├── index.md           → Section landing page
├── 01-installation.md → "Installation" in sidebar
├── 02-configuration.md → "Configuration" in sidebar
└── 03-deployment.md   → "Deployment" in sidebar
```

### Nested Sections

Create subdirectories for nested navigation:

```
content/01-guides/
├── 01-basics/
│   ├── 01-intro.md
│   └── 02-setup.md
└── 02-advanced/
    ├── 01-plugins.md
    └── 02-theming.md
```

## Numeric Prefixes

Use numeric prefixes to control ordering:

- `01-first.md` appears before `02-second.md`
- Prefixes are stripped from display names
- `01-getting-started` displays as "Getting Started"

## Markdown Features

### Standard Markdown

- **Bold** and *italic* text
- [Links](https://example.com)
- `inline code`
- Lists and blockquotes

### Code Blocks

```javascript
function hello() {
    console.log('Hello, world!');
}
```

### Tables

| Feature | Supported |
|---------|-----------|
| Tables | Yes |
| Code highlighting | Yes |
| Task lists | Yes |

### Internal Links

Link to other pages using relative paths:

```markdown
[Configuration Guide](./configuration)
[Back to Home](../index)
```

## Password Protection

To password-protect a section, create a `.protected` file:

```bash
touch content/01-internal/.protected
```

Users will need to enter the `DOCS_PASSWORD` to access that section.
