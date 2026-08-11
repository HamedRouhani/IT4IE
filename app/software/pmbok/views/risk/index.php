<?php
$pageTitle = 'ریسک‌ها - PMBOK';
$activePage = 'risk';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-exclamation-triangle"></i> مدیریت ریسک‌ها</h2>
        <p class="text-muted"><?= $stats['all_risks'] ?? 0 ?> ریسک ثبت شده</p>
    </div>
    <a href="?controller=risk&action=create" class="btn btn-danger">
        <i class="fas fa-plus-circle"></i> ریسک جدید
    </a>
</div>

<!-- آمار ریسک‌ها -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="color: #EF4444;"><i class="fas fa-fire"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['high_risks'] ?? 0 ?></div>
            <div class="stat-label">بحرانی</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: #F59E0B;"><i class="fas fa-exclamation"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['medium_risks'] ?? 0 ?></div>
            <div class="stat-label">متوسط</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: #10B981;"><i class="fas fa-check"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['low_risks'] ?? 0 ?></div>
            <div class="stat-label">کم</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-info);"><i class="fas fa-list"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
            <div class="stat-label">نتایج فیلتر</div>
        </div>
    </div>
</div>

<!-- فیلتر -->
<div class="card filter-card">
    <form method="GET" class="filter-form">
        <input type="hidden" name="controller" value="risk">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" placeholder="عنوان ریسک..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">پروژه</label>
                <select name="project_id" class="form-select">
                    <option value="">همه پروژه‌ها</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($project_id ?? 0) == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">احتمال</label>
                <select name="probability" class="form-select">
                    <option value="">همه</option>
                    <option value="very_low" <?= ($probability ?? '') === 'very_low' ? 'selected' : '' ?>>بسیار کم</option>
                    <option value="low" <?= ($probability ?? '') === 'low' ? 'selected' : '' ?>>کم</option>
                    <option value="medium" <?= ($probability ?? '') === 'medium' ? 'selected' : '' ?>>متوسط</option>
                    <option value="high" <?= ($probability ?? '') === 'high' ? 'selected' : '' ?>>بالا</option>
                    <option value="very_high" <?= ($probability ?? '') === 'very_high' ? 'selected' : '' ?>>بسیار بالا</option>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> فیلتر
                </button>
                <a href="?controller=risk" class="btn btn-secondary">
                    <i class="fas fa-times"></i> پاک کردن
                </a>
            </div>
        </div>
    </form>
</div>

<!-- لیست ریسک‌ها -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>عنوان</th>
                    <th>پروژه</th>
                    <th>احتمال</th>
                    <th>تاثیر</th>
                    <th>امتیاز</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($risks)): ?>
                    <tr><td colspan="7" class="text-center text-muted">ریسکی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($risks as $r): ?>
                    <tr>
                        <td>
                            <a href="?controller=risk&action=show&id=<?= $r['id'] ?>" style="font-weight: 600;">
                                <?= htmlspecialchars($r['title']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="?controller=project&action=show&id=<?= $r['project_id'] ?>">
                                <?= htmlspecialchars($r['project_name']) ?>
                            </a>
                        </td>
                        <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                        <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                        <td>
                            <strong style="color: <?= $r['risk_score'] >= 15 ? '#EF4444' : ($r['risk_score'] >= 8 ? '#F59E0B' : '#10B981') ?>">
                                <?= $r['risk_score'] ?>
                            </strong>
                        </td>
                        <td><span class="badge badge-info"><?= pmbok_getRiskStatusLabel($r['status']) ?></span></td>
                        <td class="actions-cell">
                            <a href="?controller=risk&action=show&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?controller=risk&action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="?controller=risk&action=delete&id=<?= $r['id'] ?>" 
                                  style="display: inline;" onsubmit="return confirm('حذف شود؟')">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>