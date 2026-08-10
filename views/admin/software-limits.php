<?php
/**
 * ویو مدیریت محدودیت‌های نرم‌افزارها - پنل ادمین
 * مسیر: views/admin/software-limits.php
 */
?>

<div class="admin-header">
    <h1><i class="fas fa-sliders-h"></i> مدیریت محدودیت‌های نرم‌افزارها</h1>
    <div class="admin-actions">
        <a href="/admin" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به داشبورد
        </a>
        <a href="/admin/software-activity" class="btn btn-primary">
            <i class="fas fa-chart-bar"></i> آمار استفاده
        </a>
    </div>
</div>

<!-- راهنما -->
<div class="card" style="background: #e8f4fd; border-right: 4px solid #3498db;">
    <p style="margin: 0;">
        <i class="fas fa-info-circle"></i>
        <strong>راهنما:</strong> محدودیت‌ها برای کاربران مهمان بر اساس IP و برای کاربران لاگین شده بر اساس user_id اعمال می‌شوند.
        مقادیر پیش‌فرض: <strong>۳ پروژه رایگان</strong> و <strong>۱۰ تحلیل نیازمندی رایگان</strong> برای هر IP/کاربر.
    </p>
</div>

<!-- آمار محدودیت‌ها -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-pie"></i> آمار محدودیت‌ها</h3>
    </div>
    
    <?php if (empty($adminStats)): ?>
        <div class="text-muted text-center" style="padding: 40px 0;">
            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
            <p style="margin-top: 15px;">هنوز هیچ محدودیتی ثبت نشده است.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>نرم‌افزار</th>
                        <th>نوع منبع</th>
                        <th>تعداد رکوردها</th>
                        <th>کل استفاده</th>
                        <th>رسیده به سقف</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adminStats as $stat): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($stat['software_slug']) ?></strong></td>
                        <td><span class="badge badge-secondary"><?= htmlspecialchars($stat['resource_type']) ?></span></td>
                        <td><?= number_format($stat['total_records']) ?></td>
                        <td><?= number_format($stat['total_usage']) ?></td>
                        <td>
                            <?php if ($stat['reached_limit_count'] > 0): ?>
                                <span class="badge badge-danger"><?= number_format($stat['reached_limit_count']) ?></span>
                            <?php else: ?>
                                <span class="badge badge-success">۰</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('آیا از ریست محدودیت‌های این نرم‌افزار اطمینان دارید؟');">
                                <input type="hidden" name="action" value="reset_software">
                                <input type="hidden" name="software_slug" value="<?= htmlspecialchars($stat['software_slug']) ?>">
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="fas fa-redo"></i> ریست
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

<!-- عملیات کلی -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-tools"></i> عملیات کلی</h3>
    </div>
    
    <div class="d-flex gap-2 flex-wrap">
        <form method="POST" onsubmit="return confirm('آیا از ریست تمام محدودیت‌ها اطمینان دارید؟ این عملیات قابل بازگشت نیست.');">
            <input type="hidden" name="action" value="reset_all">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash-restore"></i> ریست تمام محدودیت‌ها
            </button>
        </form>
    </div>
</div>

<!-- تنظیمات محدودیت‌ها -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-cog"></i> تنظیمات پیش‌فرض محدودیت‌ها</h3>
    </div>
    
    <p class="text-muted">محدودیت‌های پیش‌فرض در فایل <code>app/models/SoftwareUsageLimit.php</code> تعریف شده‌اند:</p>
    
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>نرم‌افزار</th>
                    <th>نوع منبع</th>
                    <th>حداکثر مجاز</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>babok-analyzer</strong></td>
                    <td>projects</td>
                    <td><span class="badge badge-primary">۳ پروژه</span></td>
                </tr>
                <tr>
                    <td><strong>babok-analyzer</strong></td>
                    <td>requirement_analysis</td>
                    <td><span class="badge badge-primary">۱۰ تحلیل</span></td>
                </tr>
                <tr>
                    <td><strong>pmbok-analyzer</strong></td>
                    <td>projects</td>
                    <td><span class="badge badge-secondary">۳ پروژه</span></td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <p class="text-muted" style="margin-top: 15px;">
        <i class="fas fa-info-circle"></i>
        برای تغییر محدودیت‌ها، فایل <code>SoftwareUsageLimit.php</code> را ویرایش کنید.
    </p>
</div>

<style>
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-header h1 {
    margin: 0;
    font-size: 1.8rem;
}

.admin-actions {
    display: flex;
    gap: 10px;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 25px;
}

.card-header {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.card-title {
    margin: 0;
    font-size: 1.2rem;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #2c3e50;
    color: white;
    padding: 12px 15px;
    text-align: right;
}

.table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.table tr:hover td {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-primary { background: #3498db; color: white; }
.badge-success { background: #27ae60; color: white; }
.badge-warning { background: #f39c12; color: white; }
.badge-danger { background: #e74c3c; color: white; }
.badge-secondary { background: #ecf0f1; color: #2c3e50; }

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary { background: #3498db; color: white; }
.btn-primary:hover { background: #2980b9; }
.btn-secondary { background: #ecf0f1; color: #2c3e50; }
.btn-secondary:hover { background: #d5d8dc; }
.btn-warning { background: #f39c12; color: white; }
.btn-warning:hover { background: #d68910; }
.btn-danger { background: #e74c3c; color: white; }
.btn-danger:hover { background: #c0392b; }

.text-muted { color: #95a5a6; }
.text-center { text-align: center; }
.d-flex { display: flex; }
.gap-2 { gap: 10px; }
.flex-wrap { flex-wrap: wrap; }

code {
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.85rem;
}
</style>