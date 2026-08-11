<?php
$pageTitle = 'تکنیک‌ها - PMBOK';
$activePage = 'technique';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-tools"></i> تکنیک‌های PMBOK</h2>
        <p class="text-muted"><?= $stats['all_techniques'] ?? 0 ?> تکنیک در <?= $stats['categories_count'] ?? 0 ?> دسته‌بندی</p>
    </div>
</div>

<!-- فیلتر -->
<div class="card filter-card">
    <form method="GET" class="filter-form">
        <input type="hidden" name="controller" value="technique">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" placeholder="نام تکنیک..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">دسته‌بندی</label>
                <select name="category" class="form-select">
                    <option value="">همه دسته‌ها</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['category']) ?>" 
                                <?= ($category ?? '') === $cat['category'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> فیلتر
                </button>
                <a href="?controller=technique" class="btn btn-secondary">
                    <i class="fas fa-times"></i> پاک کردن
                </a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>نام تکنیک</th>
                    <th>دسته‌بندی</th>
                    <th>تعداد فرآیندها</th>
                    <th>حوزه‌های دانشی</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($techniques)): ?>
                    <tr><td colspan="5" class="text-center text-muted">تکنیکی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($techniques as $tech): ?>
                    <tr>
                        <td>
                            <a href="?controller=technique&action=show&id=<?= $tech['id'] ?>" style="font-weight: 600;">
                                <?= htmlspecialchars($tech['name']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if (!empty($tech['category'])): ?>
                                <span class="badge badge-secondary"><?= htmlspecialchars($tech['category']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-primary"><?= $tech['task_count'] ?? 0 ?></span></td>
                        <td>
                            <small class="text-muted">
                                <?= pmbok_truncateText($tech['ka_names'] ?? '-', 40) ?>
                            </small>
                        </td>
                        <td>
                            <a href="?controller=technique&action=show&id=<?= $tech['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>