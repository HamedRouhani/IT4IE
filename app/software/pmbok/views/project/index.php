<?php if (!$isAuthenticated): ?>
<div class="card" style="margin-bottom: 20px; border-right: 5px solid var(--soft-warning); background: #FFFBEB;">
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="font-size: 2rem; color: var(--soft-warning);">
            <i class="fas fa-lock"></i>
        </div>
        <div style="flex: 1;">
            <h3 style="margin: 0 0 5px 0;">برای مشاهده پروژه‌های خود وارد شوید</h3>
            <p style="margin: 0; color: var(--gray-dark);">پروژه‌ها و اطلاعات شما فقط برای خودتان نمایش داده می‌شود.</p>
        </div>
        <a href="/login" class="btn btn-primary" style="white-space: nowrap;">
            <i class="fas fa-sign-in-alt"></i> ورود
        </a>
    </div>
</div>
<?php endif; ?>

<?php
$pageTitle = 'پروژه‌ها - PMBOK Analyzer';
$activePage = 'project';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-project-diagram"></i> مدیریت پروژه‌ها</h2>
        <p class="text-muted"><?= $stats['all_projects'] ?? 0 ?> پروژه ثبت شده</p>
    </div>
    <a href="?controller=project&action=create" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> پروژه جدید
    </a>
</div>

<!-- کارت‌های آمار -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-primary);"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['all_projects'] ?? 0 ?></div>
            <div class="stat-label">کل پروژه‌ها</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-warning);"><i class="fas fa-spinner"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['active'] ?? 0 ?></div>
            <div class="stat-label">فعال</div>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="color: var(--soft-success);"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['completed'] ?? 0 ?></div>
            <div class="stat-label">تکمیل شده</div>
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

<!-- فیلترها -->
<div class="card filter-card">
    <form method="GET" class="filter-form">
        <input type="hidden" name="controller" value="project">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" placeholder="نام پروژه..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">فاز پروژه</label>
                <select name="phase" class="form-select">
                    <option value="">همه فازها</option>
                    <option value="initiation" <?= ($phase ?? '') === 'initiation' ? 'selected' : '' ?>>آغاز</option>
                    <option value="planning" <?= ($phase ?? '') === 'planning' ? 'selected' : '' ?>>برنامه‌ریزی</option>
                    <option value="execution" <?= ($phase ?? '') === 'execution' ? 'selected' : '' ?>>اجرا</option>
                    <option value="monitoring_controlling" <?= ($phase ?? '') === 'monitoring_controlling' ? 'selected' : '' ?>>نظارت و کنترل</option>
                    <option value="closure" <?= ($phase ?? '') === 'closure' ? 'selected' : '' ?>>اختتام</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">متدولوژی</label>
                <select name="methodology" class="form-select">
                    <option value="">همه متدولوژی‌ها</option>
                    <option value="waterfall" <?= ($methodology ?? '') === 'waterfall' ? 'selected' : '' ?>>آبشاری</option>
                    <option value="agile" <?= ($methodology ?? '') === 'agile' ? 'selected' : '' ?>>چابک</option>
                    <option value="hybrid" <?= ($methodology ?? '') === 'hybrid' ? 'selected' : '' ?>>ترکیبی</option>
                    <option value="adaptive" <?= ($methodology ?? '') === 'adaptive' ? 'selected' : '' ?>>تطبیقی</option>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> اعمال فیلتر
                </button>
                <a href="?controller=project" class="btn btn-secondary">
                    <i class="fas fa-times"></i> پاک کردن
                </a>
            </div>
        </div>
    </form>
</div>

<!-- لیست پروژه‌ها -->
<div class="card">
    <h3 class="card-title">لیست پروژه‌ها</h3>
    <?php if (empty($projects)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <p>پروژه‌ای یافت نشد.</p>
            <a href="?controller=project&action=create" class="btn btn-primary">ایجاد پروژه جدید</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>نام پروژه</th>
                        <th>فاز</th>
                        <th>متدولوژی</th>
                        <th>فرآیندها</th>
                        <th>ریسک‌ها</th>
                        <th>تحویل‌دادنی‌ها</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <a href="?controller=project&action=show&id=<?= $project['id'] ?>" style="font-weight: 600; color: #2D3748; text-decoration: none;">
                                <?= htmlspecialchars($project['name']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-<?= pmbok_getPhaseColor($project['phase']) ?>">
                                <?= pmbok_getPhaseLabel($project['phase']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology']) ?></span>
                        </td>
                        <td><span class="badge"><?= $project['task_count'] ?? 0 ?></span></td>
                        <td><span class="badge badge-warning"><?= $project['risk_count'] ?? 0 ?></span></td>
                        <td><span class="badge"><?= $project['deliverable_count'] ?? 0 ?></span></td>
                        <td><?= pmbok_showDate($project['created_at']) ?></td>
                        <td class="actions-cell">
                            <a href="?controller=project&action=show&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary" title="مشاهده">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="?controller=project&action=edit&id=<?= $project['id'] ?>" class="btn btn-sm btn-warning" title="ویرایش">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="?controller=project&action=delete&id=<?= $project['id'] ?>" 
                                  style="display: inline;" 
                                  onsubmit="return confirm('آیا مطمئن هستید؟')">
                                <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>