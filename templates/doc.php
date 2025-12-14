<?php
// Get current section name for breadcrumb
$currentSectionName = '';
foreach ($sections as $s) {
    if ($s['slug'] === $currentSection) {
        $currentSectionName = $s['name'];
        break;
    }
}
?>
<?php if (!empty($breadcrumb)): ?>
<nav class="breadcrumb">
    <a href="/docs/<?= htmlspecialchars($currentSection) ?>"><?= htmlspecialchars($currentSectionName) ?></a>
    <?php foreach ($breadcrumb as $crumb): ?>
    <span class="breadcrumb-separator">/</span>
    <a href="/docs/<?= htmlspecialchars($crumb['slug']) ?>"><?= htmlspecialchars($crumb['name']) ?></a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<article class="doc-content">
    <?= $html ?>
</article>
