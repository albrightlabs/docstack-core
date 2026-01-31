# Deployment Guide

This guide covers deploying Docstack to a production server with Laravel Forge, ensuring content changes made on the server are preserved across deployments.

## The Problem

Docstack stores content in the `content/` directory and user accounts in `data/users.json`. When deploying new code, these could be overwritten. This guide solves that with a pre-deploy sync strategy.

## Strategy Overview

1. **User accounts** (`data/users.json`) are gitignored and copied between releases
2. **Content** stays in git, with server changes synced back before each deployment
3. Automated commits use `[skip ci]` to prevent deployment loops

## Prerequisites

- Laravel Forge (or similar deployment platform)
- GitHub repository for your Docstack instance
- SSH access to your server

## User Accounts

User accounts are environment-specific. Each server has its own users.

### How It Works

- `data/users.json` is gitignored
- `data/users.json.example` provides an empty template
- Deploy script copies users from previous release
- First deploy creates empty users file (run setup wizard to create admin)

### No Action Required

This is handled automatically by the deploy script below.

## Content Persistence

Content edits made directly on the server are synced to git before each deployment.

### One-Time Server Setup

SSH into your server and create a persistent repo directory:

```bash
# Replace with your domain
DOMAIN="docs.yourdomain.com"

# Create repo directory
mkdir -p /home/forge/$DOMAIN/repo

# Clone your Docstack repo
git clone https://github.com/your-org/your-docstack.git /home/forge/$DOMAIN/repo

# Set up git credentials for pushing
git config --global credential.helper store

# Create credentials file (use a GitHub Personal Access Token)
echo "https://your-username:YOUR_GITHUB_PAT@github.com" > /home/forge/.git-credentials
chmod 600 /home/forge/.git-credentials

# Test that push works
cd /home/forge/$DOMAIN/repo
git fetch origin
```

### Deploy Script

Use this as your Forge deploy script (replace `docs.yourdomain.com` with your domain):

```bash
$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

# ═══════════════════════════════════════════════════════════════════════════════
# PRE-DEPLOY: Sync server content changes to git
# ═══════════════════════════════════════════════════════════════════════════════
DOMAIN="docs.yourdomain.com"
CURRENT_CONTENT="/home/forge/$DOMAIN/current/content"
REPO_DIR="/home/forge/$DOMAIN/repo"

if [ -d "$CURRENT_CONTENT" ] && [ -d "$REPO_DIR" ]; then
    echo "Syncing server content changes..."

    cd "$REPO_DIR"
    git fetch origin
    git checkout main
    git pull origin main

    # Sync content from current deployment to repo
    rsync -a --delete "$CURRENT_CONTENT/" "$REPO_DIR/content/"

    # Check for changes and commit if any
    if [ -n "$(git status --porcelain content/)" ]; then
        git config user.name "Docstack Deploy"
        git config user.email "deploy@yourdomain.com"
        git add content/
        git commit -m "Auto-sync server content [skip ci]"
        git push origin main
        echo "Server content synced to git."
    else
        echo "No content changes to sync."
    fi

    cd $FORGE_RELEASE_DIRECTORY
fi

# ═══════════════════════════════════════════════════════════════════════════════
# INSTALL: Dependencies
# ═══════════════════════════════════════════════════════════════════════════════
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ═══════════════════════════════════════════════════════════════════════════════
# SETUP: Directories and permissions
# ═══════════════════════════════════════════════════════════════════════════════
mkdir -p content
mkdir -p data
mkdir -p public/uploads
chmod -R 775 content
chmod -R 775 data
chmod -R 775 public/uploads

# ═══════════════════════════════════════════════════════════════════════════════
# SETUP: User data (environment-specific)
# ═══════════════════════════════════════════════════════════════════════════════
# Copy users from previous release (preserves accounts across deploys)
PREVIOUS_USERS="/home/forge/$DOMAIN/current/data/users.json"
if [ -f "$PREVIOUS_USERS" ]; then
    cp "$PREVIOUS_USERS" data/users.json
    echo "Preserved user accounts from previous release."
elif [ -f "data/users.json.example" ]; then
    cp data/users.json.example data/users.json
    echo "Created fresh users.json from template."
fi

$ACTIVATE_RELEASE()
```

### Forge Configuration

Configure Forge to ignore automated commits:

1. Go to your site in Forge
2. Navigate to Deployments
3. Set deployment trigger to ignore commits containing `[skip ci]`

This prevents the auto-sync commit from triggering another deployment.

## How It Works

### Normal Developer Workflow

1. Developer commits locally: `git commit -m "Update documentation"`
2. Push to GitHub
3. Forge triggers deployment
4. Deploy script:
   - Syncs any server content changes to git first
   - Pulls new code (including synced content)
   - Preserves user accounts
   - Activates new release

### Server Content Changes

1. Admin edits content via Docstack's built-in editor
2. Next deployment (from any push):
   - Deploy script detects server changes
   - Commits them with `[skip ci]`
   - Pushes to GitHub (no loop triggered)
   - Deploys the original changes
3. Content is now in git for all environments

### First Deployment

1. Deploy runs, no previous content to sync
2. Fresh `users.json` created from template
3. Visit site to run setup wizard and create admin account

## Troubleshooting

### Content not syncing

Check repo directory exists and has proper permissions:

```bash
ls -la /home/forge/docs.yourdomain.com/repo
```

### Git push failing

Verify credentials:

```bash
cat /home/forge/.git-credentials
git -C /home/forge/docs.yourdomain.com/repo fetch origin
```

### Deploy loops

Ensure Forge ignores `[skip ci]` commits. Check GitHub commits to confirm the marker is present in automated commits.

### Lost user accounts after deploy

Check that the copy command is finding the previous users file:

```bash
ls -la /home/forge/docs.yourdomain.com/current/data/
```

## Alternative: Symlink Approach

If you prefer, you can use symlinks instead of the pre-deploy sync:

```bash
# In deploy script, replace content sync with:
rm -rf content
rm -rf data
ln -s /home/forge/$DOMAIN/persistent/content content
ln -s /home/forge/$DOMAIN/persistent/data data
```

This keeps content/data outside deployments entirely. Sync to git separately via cron if desired.

## Security Notes

- Keep `.git-credentials` permissions at 600
- Use a GitHub PAT with minimal permissions (repo access only)
- Consider using deploy keys instead of PATs for tighter security
