<?php
$pageTitle = 'تکنیک‌های BABOK - BABOK Analyzer';
$activePage = 'techniques';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-tools"></i> تکنیک‌های BABOK
        </div>
        <div>
            <span class="badge badge-primary"><?= count($techniques) ?> تکنیک</span>
        </div>
    </div>

    <!-- جستجو و فیلتر -->
    <div class="d-flex gap-2 flex-wrap" style="margin-bottom: 15px;">
        <input type="text" id="searchTechnique" placeholder="جستجوی تکنیک..." onkeyup="filterTechniques()" 
               style="flex: 1; padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; min-width: 150px;">
        <select id="filterCategory" onchange="filterTechniques()" 
                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.9rem; min-width: 150px;">
            <option value="all">همه دسته‌ها</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['category'] ?>"><?= ucfirst($cat['category']) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" onclick="resetFilters()">
            <i class="fas fa-undo"></i> حذف فیلتر
        </button>
    </div>

    <!-- گرید تکنیک‌ها -->
    <div class="techniques-grid" id="techniquesGrid">
        <?php foreach ($techniques as $tech): ?>
        <div class="technique-card" data-category="<?= $tech['category'] ?>" data-name="<?= strtolower($tech['name']) ?>"
             style="background: white; border: 1px solid #f0f0f0; border-radius: 12px; padding: 16px; transition: 0.3s; box-shadow: var(--shadow); display: flex; flex-direction: column; height: 100%;">
            
            <!-- نام و دسته‌بندی -->
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                <div style="font-weight: 700; font-size: 1.05rem;"><?= htmlspecialchars($tech['name']) ?></div>
                <span class="badge <?= match($tech['category']) {
                    'collaborative' => 'badge-info',
                    'strategic' => 'badge-danger',
                    'modeling' => 'badge-primary',
                    'management' => 'badge-secondary',
                    'research' => 'badge-success',
                    'experimental' => 'badge-warning',
                    default => 'badge-secondary'
                } ?>">
                    <?= ucfirst($tech['category']) ?>
                </span>
            </div>
            
            <!-- توضیحات کامل -->
            <div style="font-size: 0.85rem; color: #555; flex: 1; margin-bottom: 10px; line-height: 1.6;">
                <?php 
                $description = $tech['purpose'] ?? $tech['description'] ?? '';
                if (!empty($description)) {
                    // نمایش کامل توضیحات
                    if (strlen($description) > 120) {
                        echo '<div style="max-height: 72px; overflow: hidden; position: relative;" class="desc-short">';
                        echo '<span>' . htmlspecialchars(substr($description, 0, 120)) . '...</span>';
                        echo '<button onclick="this.parentElement.style.maxHeight=\'none\'; this.parentElement.nextElementSibling.style.display=\'block\'; this.style.display=\'none\'" 
                                style="background: none; border: none; color: var(--secondary-color); cursor: pointer; font-size: 0.75rem; display: block; margin-top: 3px;">
                                <i class="fas fa-chevron-down"></i> نمایش کامل
                              </button>';
                        echo '</div>';
                        echo '<div style="display: none;" class="desc-full">' . nl2br(htmlspecialchars($description)) . '</div>';
                    } else {
                        echo nl2br(htmlspecialchars($description));
                    }
                } else {
                    echo '<span class="text-muted">بدون توضیحات</span>';
                }
                ?>
            </div>
            
            <!-- دکمه مشاهده جزئیات -->
            <div style="margin-top: auto; padding-top: 10px; border-top: 1px solid #f0f0f0;">
                <a href="/babok/public/?route=techniques_view&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary" style="width: 100%; text-align: center;">
                    <i class="fas fa-eye"></i> مشاهده جزئیات
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function filterTechniques() {
    const category = document.getElementById('filterCategory').value;
    const search = document.getElementById('searchTechnique').value.toLowerCase();
    const items = document.querySelectorAll('.technique-card');
    
    items.forEach(item => {
        const itemCategory = item.dataset.category;
        const itemName = item.dataset.name;
        let show = true;
        
        if (category !== 'all' && itemCategory != category) show = false;
        if (search && !itemName.includes(search)) show = false;
        
        item.style.display = show ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('filterCategory').value = 'all';
    document.getElementById('searchTechnique').value = '';
    filterTechniques();
}
</script>

<style>
/* استایل برای نمایش بهتر توضیحات طولانی */
.desc-short {
    transition: max-height 0.3s ease;
}
.desc-full {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 6px;
    margin-top: 5px;
    border-right: 3px solid var(--secondary-color);
    font-size: 0.85rem;
    line-height: 1.7;
}
.technique-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.technique-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}
.techniques-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}
@media (max-width: 768px) {
    .techniques-grid {
        grid-template-columns: 1fr;
    }
}
</style>