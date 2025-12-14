<?php

/**
 * Render a sidebar tree recursively with admin controls
 */
function renderTree(array $items, string $currentPath, string $parentSlug = '', int $depth = 0): void
{
    foreach ($items as $item):
        $isActive = $currentPath === $item['slug'];
        $itemName = htmlspecialchars($item['name']);
        $itemSlug = htmlspecialchars($item['slug']);
?>
        <?php if ($item['type'] === 'dir'): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-header">
                <span><?= $itemName ?></span>
                <span class="sidebar-item-actions admin-only" style="display: none;">
                    <button class="sidebar-action-btn" onclick="event.stopPropagation(); AdminEditor.showCreateModal('<?= $itemSlug ?>')" title="Add new">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    </button>
                    <button class="sidebar-action-btn" onclick="event.stopPropagation(); AdminEditor.showRenameModal('<?= $itemSlug ?>', '<?= $itemName ?>')" title="Rename">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button class="sidebar-action-btn" onclick="event.stopPropagation(); AdminEditor.showDeleteModal('<?= $itemSlug ?>', '<?= $itemName ?>')" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </span>
            </div>
            <div class="sidebar-section-content">
                <?php renderTree($item['children'], $currentPath, $item['slug'], $depth + 1); ?>
            </div>
        </div>
        <?php else: ?>
        <div class="sidebar-link-wrapper">
            <a href="/docs/<?= $itemSlug ?>" class="sidebar-link<?= $isActive ? ' active' : '' ?>">
                <?= $itemName ?>
                <span class="sidebar-item-actions admin-only" style="display: none;">
                    <button class="sidebar-action-btn" onclick="event.preventDefault(); event.stopPropagation(); AdminEditor.enterEditMode('<?= $itemSlug ?>')" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button class="sidebar-action-btn" onclick="event.preventDefault(); event.stopPropagation(); AdminEditor.showRenameModal('<?= $itemSlug ?>', '<?= $itemName ?>')" title="Rename">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                    <button class="sidebar-action-btn" onclick="event.preventDefault(); event.stopPropagation(); AdminEditor.showDeleteModal('<?= $itemSlug ?>', '<?= $itemName ?>')" title="Delete">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </span>
            </a>
        </div>
        <?php endif; ?>
<?php
    endforeach;
}
?>

<!-- Admin action buttons at top of sidebar -->
<div class="sidebar-admin-actions admin-only" style="display: none;">
    <button class="admin-btn admin-btn-primary admin-btn-sm" onclick="AdminEditor.showCreateModal('<?= htmlspecialchars($currentSection ?? '') ?>')">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        New Page
    </button>
    <button class="admin-btn admin-btn-secondary admin-btn-sm" onclick="AdminEditor.showCreateModal('<?= htmlspecialchars($currentSection ?? '') ?>')" data-directory="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
        New Folder
    </button>
</div>

<?php
// Render the tree
renderTree($tree, $currentPath, $currentSection ?? '');
?>
