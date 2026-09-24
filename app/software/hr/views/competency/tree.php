<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-sitemap"></i> درخت شایستگی‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong style="color: var(--hr-success);"><?= hr_num($stats['active']) ?></strong>
                — هسته‌ای: <strong style="color: var(--hr-warning);"><?= hr_num($stats['core_count']) ?></strong>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('competency') ?>" class="btn-hr-outline">
                <i class="fas fa-list"></i> نمای فهرست
            </a>
            <a href="<?= hr_url('competency', 'create') ?>" class="btn-hr-primary">
                <i class="fas fa-plus"></i> شایستگی جدید
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-tree"></i> ساختار درختی</h3></div>
        <div class="card-body">
            <?php if (empty($tree)): ?>
                <div class="hr-empty-state">
                    <i class="fas fa-cubes"></i>
                    <h4>هیچ شایستگی‌ای ثبت نشده است</h4>
                    <a href="<?= hr_url('competency', 'create') ?>" class="btn-hr-primary">
                        <i class="fas fa-plus"></i> شایستگی جدید
                    </a>
                </div>
            <?php else: ?>
                <ul class="hr-tree">
                    <?php foreach ($tree as $node): ?>
                        <?= renderCompetencyNode($node, $categoryOptions) ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php
/**
 * رندر بازگشتی گره درخت
 */
function renderCompetencyNode(array $node, array $categoryOptions): string
{
    $hasChildren = !empty($node['children']);
    $url = hr_url('competency', 'show', ['id' => $node['id']]);

    $html = '<li>';
    $html .= '<div class="hr-tree-node">';

    if ($hasChildren) {
        $html .= '<span class="hr-tree-toggle"><i class="fas fa-chevron-down"></i></span>';
    } else {
        $html .= '<span class="hr-tree-toggle hr-tree-toggle-empty"></span>';
    }

    $html .= '<i class="fas fa-cube" style="color: var(--hr-primary); margin: 0 0.5rem;"></i>';

    $html .= '<a href="' . $url . '" class="hr-tree-link">';
    $html .= hr_e($node['name']);
    $html .= '</a>';

    if (!empty($node['code'])) {
        $html .= ' <code class="hr-text-muted" style="font-size: 0.75rem; margin-right: 0.5rem;">' . hr_e($node['code']) . '</code>';
    }

    $catClass = \App\Software\Hr\Models\Competency::getCategoryClass($node['category']);
    $catLabel = $categoryOptions[$node['category']] ?? $node['category'];
    $html .= ' <span class="hr-status-badge ' . $catClass . '" style="font-size: 0.7rem;">' . hr_e($catLabel) . '</span>';

    if (!empty($node['is_core'])) {
        $html .= ' <span class="hr-status-badge hr-status-danger" style="font-size: 0.7rem;"><i class="fas fa-star"></i></span>';
    }

    $html .= '</div>';

    if ($hasChildren) {
        $html .= '<ul>';
        foreach ($node['children'] as $child) {
            $html .= renderCompetencyNode($child, $categoryOptions);
        }
        $html .= '</ul>';
    }

    $html .= '</li>';
    return $html;
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.hr-tree-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            var li = this.closest('li');
            if (!li) return;
            var sublist = li.querySelector(':scope > ul');
            if (!sublist) return;
            var icon = this.querySelector('i');
            if (sublist.style.display === 'none') {
                sublist.style.display = '';
                if (icon) icon.className = 'fas fa-chevron-down';
            } else {
                sublist.style.display = 'none';
                if (icon) icon.className = 'fas fa-chevron-left';
            }
        });
    });
});
</script>

<script src="/public/assets/js/software/hr.js"></script>