<?php
/**
 * ویو لیست تکنیک‌های BABOK
 * مسیر: app/software/babok/views/techniques/index.php
 */
$pageTitle = 'تکنیک‌های BABOK - BABOK Analyzer';
$activePage = 'techniques';

// دسته‌بندی‌ها با برچسب فارسی
$categoryLabels = [
    'collaborative' => 'همکاری',
    'research' => 'تحقیقاتی',
    'experimental' => 'آزمایشی',
    'management' => 'مدیریتی',
    'strategic' => 'استراتژیک',
    'modeling' => 'مدل‌سازی'
];
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> تکنیک‌های استاندارد BABOK (<?= count($techniques) ?>)
        </div>
    </div>
    
    <!-- جستجو و فیلتر -->
    <div class="d-flex gap-2 flex-wrap" style="margin-bottom: 20px;">
        <input type="text" id="searchTechnique" class="form-control" 
               placeholder="🔍 جستجوی تکنیک..." style="flex: 1; min-width: 250px;">
        <select id="filterCategory" class="form-control" style="width: 200px;">
            <option value="">همه دسته‌ها</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat ?>"><?= $categoryLabels[$cat] ?? $cat ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <!-- گرید تکنیک‌ها -->
    <div class="techniques-grid" id="techniquesGrid">
        <?php if (empty($techniques)): ?>
            <p class="text-muted">هیچ تکنیکی یافت نشد.</p>
        <?php else: ?>
            <?php foreach ($techniques as $tech): ?>
                <div class="technique-card" data-category="<?= $tech['category'] ?>" data-name="<?= mb_strtolower($tech['name']) ?>">
                    <span class="technique-category category-<?= $tech['category'] ?>">
                        <?= $categoryLabels[$tech['category']] ?? $tech['category'] ?>
                    </span>
                    <h4><?= htmlspecialchars($tech['name']) ?></h4>
                    <?php if (!empty($tech['purpose'])): ?>
                        <p style="font-size: 0.85rem; color: #666; margin-top: 8px; line-height: 1.6;">
                            <?= mb_substr(htmlspecialchars($tech['purpose']), 0, 120) ?><?= mb_strlen($tech['purpose']) > 120 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>
                    <div style="margin-top: 12px;">
                        <a href="?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> مشاهده جزئیات
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- آمار دسته‌بندی‌ها -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 class="card-title"><i class="fas fa-chart-bar"></i> آمار دسته‌بندی تکنیک‌ها</h4>
    <div class="row" style="margin-top: 15px;">
        <?php 
        $categoryCounts = [];
        foreach ($techniques as $tech) {
            $cat = $tech['category'];
            $categoryCounts[$cat] = ($categoryCounts[$cat] ?? 0) + 1;
        }
        foreach ($categoryCounts as $cat => $count): 
        ?>
            <div class="card" style="margin-bottom: 0; text-align: center;">
                <span class="badge category-<?= $cat ?>" style="font-size: 0.85rem; padding: 6px 15px;">
                    <?= $categoryLabels[$cat] ?? $cat ?>
                </span>
                <div style="font-size: 1.8rem; font-weight: 700; margin-top: 8px;"><?= $count ?></div>
                <div class="text-muted" style="font-size: 0.8rem;">تکنیک</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// جستجو و فیلتر تکنیک‌ها
const searchInput = document.getElementById('searchTechnique');
const categoryFilter = document.getElementById('filterCategory');
const cards = document.querySelectorAll('#techniquesGrid .technique-card');

function filterTechniques() {
    const searchTerm = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;
    
    cards.forEach(card => {
        const name = card.dataset.name;
        const category = card.dataset.category;
        
        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesCategory = !selectedCategory || category === selectedCategory;
        
        card.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
    });
}

searchInput.addEventListener('input', filterTechniques);
categoryFilter.addEventListener('change', filterTechniques);
</script>