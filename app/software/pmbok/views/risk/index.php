<?php
/** لیست ریسک‌ها - PMBOK (بازطراحی‌شده، بدون استایل inline) */
$pageTitle  = 'ریسک‌ها - PMBOK';
$activePage = 'risk';

$stats       = is_array($stats ?? null) ? $stats : [];
$risks       = $risks ?? [];
$projects    = $projects ?? [];
$search      = $search ?? '';
$project_id  = (int)($project_id ?? 0);
$probability = $probability ?? '';

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$levels = ['very_low' => 'بسیار کم', 'low' => 'کم', 'medium' => 'متوسط', 'high' => 'بالا', 'very_high' => 'بسیار بالا'];
$tone = static fn($score) => $score >= 15 ? 'high' : ($score >= 8 ? 'mid' : 'low');
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-exclamation-triangle"></i> مدیریت ریسک‌ها</h2>
        <p class="text-muted"><?= (int)($stats['all_risks'] ?? 0) ?> ریسک ثبت شده</p>
    </div>
    <a href="?controller=risk&action=create" class="btn btn-danger"><i class="fas fa-plus-circle"></i> ریسک جدید</a>
</div>

<!-- آمار -->
<div class="stats-grid">
    <div class="card stat-card stat-card--danger">
        <div class="stat-icon" aria-hidden="true"><i class="fas fa-fire"></i></div>
        <div class="stat-info"><div class="stat-value"><?= (int)($stats['high_risks'] ?? 0) ?></div><div class="stat-label">بحرانی</div></div>
    </div>
    <div class="card stat-card stat-card--warning">
        <div class="stat-icon" aria-hidden="true"><i class="fas fa-exclamation"></i></div>
        <div class="stat-info"><div class="stat-value"><?= (int)($stats['medium_risks'] ?? 0) ?></div><div class="stat-label">متوسط</div></div>
    </div>
    <div class="card stat-card stat-card--success">
        <div class="stat-icon" aria-hidden="true"><i class="fas fa-check"></i></div>
        <div class="stat-info"><div class="stat-value"><?= (int)($stats['low_risks'] ?? 0) ?></div><div class="stat-label">کم</div></div>
    </div>
    <div class="card stat-card stat-card--info">
        <div class="stat-icon" aria-hidden="true"><i class="fas fa-list"></i></div>
        <div class="stat-info"><div class="stat-value"><?= (int)($stats['total'] ?? 0) ?></div><div class="stat-label">نتایج فیلتر</div></div>
    </div>
</div>

<!-- فیلتر -->
<section class="card filter-card">
    <form method="GET" class="filter-form">
        <input type="hidden" name="controller" value="risk">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label" for="f_search">جستجو</label>
                <input type="text" id="f_search" name="search" class="form-control" placeholder="عنوان ریسک..." value="<?= $e($search) ?>">
            </div>
            <div class="form-group">
                <label class="form-label" for="f_project">پروژه</label>
                <select id="f_project" name="project_id" class="form-select">
                    <option value="">همه پروژه‌ها</option>
                    <?php foreach ($projects as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= $project_id === (int)$p['id'] ? 'selected' : '' ?>><?= $e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="f_prob">احتمال</label>
                <select id="f_prob" name="probability" class="form-select">
                    <option value="">همه</option>
                    <?php foreach ($levels as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $probability === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> فیلتر</button>
                <a href="?controller=risk" class="btn btn-secondary"><i class="fas fa-times"></i> پاک کردن</a>
            </div>
        </div>
    </form>
</section>

<!-- لیست -->
<section class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>عنوان</th><th>پروژه</th><th>احتمال</th><th>تاثیر</th><th>امتیاز</th><th>وضعیت</th><th class="col-actions">عملیات</th></tr>
            </thead>
            <tbody>
            <?php if (empty($risks)): ?>
                <tr><td colspan="7" class="empty-note">ریسکی یافت نشد.</td></tr>
            <?php else: foreach ($risks as $r): $score = (int)$r['risk_score']; ?>
                <tr>
                    <td><a href="?controller=risk&action=show&id=<?= (int)$r['id'] ?>" class="link-strong"><?= $e($r['title']) ?></a></td>
                    <td><a href="?controller=project&action=show&id=<?= (int)$r['project_id'] ?>"><?= $e($r['project_name']) ?></a></td>
                    <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                    <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                    <td><strong class="score score--<?= $tone($score) ?>"><?= $score ?></strong></td>
                    <td><span class="badge badge-info"><?= pmbok_getRiskStatusLabel($r['status']) ?></span></td>
                    <td class="col-actions">
                        <a href="?controller=risk&action=show&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده" aria-label="مشاهده"><i class="fas fa-eye"></i></a>
                        <a href="?controller=risk&action=edit&id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning" title="ویرایش" aria-label="ویرایش"><i class="fas fa-edit"></i></a>
                        <form method="POST" class="inline-form-btn" action="?controller=risk&action=delete&id=<?= (int)$r['id'] ?>" data-confirm="این ریسک حذف شود؟">
                            <button type="submit" class="btn btn-sm btn-danger" title="حذف" aria-label="حذف"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// تأیید حذف (بدون onsubmit inline)
document.addEventListener('submit', function (ev) {
    var msg = ev.target.getAttribute && ev.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) ev.preventDefault();
});
</script>
