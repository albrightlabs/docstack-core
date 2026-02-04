<?php
/**
 * Shared header template
 *
 * Expected variables:
 * - $branding (array) - Site branding config
 * - $basePath (string) - Base URL path
 * - $sections (array) - Content sections for tabs
 * - $currentSection (string|null) - Currently active section slug
 * - $currentPath (string|null) - Current content path (for edit button)
 * - $currentUser (array|null) - Current logged-in user
 * - $showEditButton (bool) - Whether to show the edit button (default: false)
 * - $showSidebar (bool) - Whether page has sidebar/mobile menu (default: false)
 */

$showEditButton = $showEditButton ?? false;
$showSidebar = $showSidebar ?? false;
$currentSection = $currentSection ?? null;
$currentPath = $currentPath ?? '';
$currentUser = $currentUser ?? \App\Auth::getCurrentUser();
?>
<header class="site-header">
    <div class="header-content">
        <div style="display: flex; align-items: center;">
            <?php if ($showSidebar): ?>
            <button class="mobile-menu-btn" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <?php endif; ?>
            <a href="<?= $basePath ?: '/' ?>" class="site-logo">
                <?php if (!empty($branding['logo_url'])): ?>
                <img src="<?= htmlspecialchars($branding['logo_url']) ?>" alt="<?= htmlspecialchars($branding['site_name']) ?>" style="height: 24px; width: auto;">
                <?php else: ?>
                <span class="site-logo-emoji"><?= $branding['site_emoji'] ?></span>
                <span><?= htmlspecialchars($branding['site_name']) ?></span>
                <?php endif; ?>
            </a>
        </div>
        <div class="header-nav">
            <nav class="header-tabs">
                <?php foreach ($sections as $section): ?>
                <a href="<?= $basePath ?>/<?= htmlspecialchars($section['slug']) ?>"
                   class="header-tab<?= $currentSection === $section['slug'] ? ' active' : '' ?>">
                    <?= htmlspecialchars($section['name']) ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="header-right">
                <?php if ($showEditButton && \App\Config::feature('editing') && \App\Auth::canEditContent()): ?>
                <div class="admin-controls">
                    <span id="admin-badge" class="admin-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Admin
                    </span>
                    <button id="admin-edit-btn" class="admin-btn admin-btn-ghost admin-btn-sm" onclick="AdminEditor.enterEditMode('<?= htmlspecialchars($currentPath) ?>')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                        Edit
                    </button>
                </div>
                <?php endif; ?>
                <?php if (!empty($branding['external_link_url'])): ?>
                <a href="<?= htmlspecialchars($branding['external_link_url']) ?>" class="header-external-link" target="_blank" rel="noopener noreferrer">
                    <?php if (!empty($branding['external_link_logo'])): ?>
                    <img src="<?= htmlspecialchars($branding['external_link_logo']) ?>" alt="<?= htmlspecialchars($branding['external_link_name']) ?>" width="16" height="16">
                    <?php endif; ?>
                    <?= htmlspecialchars($branding['external_link_name']) ?> &rarr;
                </a>
                <?php endif; ?>
                <?php if (\App\Auth::check()): ?>
                <div class="user-menu" id="user-menu">
                    <button type="button" class="user-menu-toggle" id="user-menu-toggle">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-menu-dropdown" id="user-menu-dropdown">
                        <div class="user-menu-info">
                            <span class="user-menu-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></span>
                        </div>
                        <?php if (\App\Auth::isAdmin()): ?>
                        <a href="<?= $basePath ?>/users" class="user-menu-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            Manage Users
                        </a>
                        <?php endif; ?>
                        <?php if (\App\Auth::canWrite()): ?>
                        <a href="#" class="user-menu-item" onclick="event.preventDefault(); if(typeof AdminEditor !== 'undefined') AdminEditor.showCleanupModal();">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Clean Up Uploads
                        </a>
                        <?php endif; ?>
                        <a href="#" class="user-menu-item" onclick="event.preventDefault(); if(typeof showChangePasswordModal !== 'undefined') showChangePasswordModal();">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Change Password
                        </a>
                        <a href="<?= $basePath ?>/logout" class="user-menu-item user-menu-item-danger">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            Sign Out
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= $basePath ?>/login" class="admin-btn admin-btn-ghost admin-btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" /></svg>
                    Sign In
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
