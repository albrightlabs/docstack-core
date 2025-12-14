# Two-Repo Setup Plan

This plan outlines how to set up docstack-core (public framework) and brightwiki-core (private instance) as separate repositories.

---

## Prerequisites

- Finalize all framework changes in this repo first
- Ensure `content/_example/` has good example documentation
- Ensure `.env.example` is comprehensive

---

## Step 1: Finalize docstack-core

```bash
cd "/Users/joebuonocore/Localhost/workspace/Albright Labs Software/md-explorer"

# Initialize git (if not already)
git init
git add .
git commit -m "Initial docstack-core framework"

# Create public GitHub repo and push
gh repo create docstack-core --public --source=. --push
```

---

## Step 2: Create brightwiki-core

```bash
# Go to workspace
cd "/Users/joebuonocore/Localhost/workspace/Albright Labs Software"

# Clone docstack-core as starting point
cp -r md-explorer brightwiki-core
cd brightwiki-core

# Remove git history (fresh start)
rm -rf .git

# Move backed-up content into place
rm -rf content/_example
mv _content_backup/* content/
rmdir _content_backup

# Delete this plan file (not needed in instance)
rm REPO-SETUP-PLAN.md
```

---

## Step 3: Configure brightwiki-core

### Update .gitignore

Remove these lines (around lines 20-21):

```diff
- content/*
- !content/_example/
```

### Create .env

```bash
cp .env.example .env
```

Edit `.env` with your company branding:

```env
SITE_NAME="BrightWiki"
SITE_EMOJI="your-emoji"
LOGO_URL="/assets/logo.png"
FOOTER_TEXT="© 2025 Your Company"
ADMIN_PASSWORD="secure-password-here"
DOCS_PASSWORD="optional-reader-password"
```

### Optional: Add custom styles

Create `public/assets/custom.css` for company-specific styling.

---

## Step 4: Push brightwiki-core

```bash
git init
git add .
git commit -m "Initial brightwiki-core setup"
gh repo create brightwiki-core --private --source=. --push
```

---

## Ongoing Workflow

### Pulling framework updates into brightwiki-core

```bash
cd brightwiki-core

# Add docstack-core as upstream remote (one-time)
git remote add upstream https://github.com/YOUR-ORG/docstack-core.git

# Fetch and merge updates
git fetch upstream
git merge upstream/main --allow-unrelated-histories

# Resolve any conflicts (usually in .gitignore, .env.example)
```

### Contributing improvements back to docstack-core

If you make framework improvements in brightwiki-core:

1. Cherry-pick or manually copy changes to docstack-core
2. Ensure no company-specific content is included
3. Commit and push to docstack-core

---

## File Differences Between Repos

| File | docstack-core | brightwiki-core |
|------|---------------|-----------------|
| `content/` | `_example/` only | Your actual docs |
| `.env` | Not tracked | Your config |
| `.gitignore` | Ignores content/* | Tracks content |
| `public/assets/custom.css` | Not tracked | Your styles |
| `_content_backup/` | Your backup (delete before push) | N/A |
