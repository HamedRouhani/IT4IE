<?php
$pageTitle = 'داشبورد QMS - ISO 9001:2015';
$activePage = 'dashboard';
?>

<!-- کارت‌های آمار -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <div class="card stat-card" style="border-right: 5px solid #6C3CE1; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #6C3CE1; margin-bottom: 10px;">
            <i class="fas fa-book"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['total_clauses'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            بندهای استاندارد
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid #10B981; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #10B981; margin-bottom: 10px;">
            <i class="fas fa-building"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['total_departments'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            واحدهای سازمانی
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid #3B82F6; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #3B82F6; margin-bottom: 10px;">
            <i class="fas fa-user-check"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['total_auditors'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            ممیزان
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid #F59E0B; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #F59E0B; margin-bottom: 10px;">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['scheduled_audits'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            ممیزی‌های زمان‌بندی شده
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid #EF4444; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #EF4444; margin-bottom: 10px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['open_ncs'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            عدم انطباق باز
        </div>
    </div>
    
    <div class="card stat-card" style="border-right: 5px solid #8B5CF6; text-align: center; padding: 25px;">
        <div style="font-size: 2.5rem; color: #8B5CF6; margin-bottom: 10px;">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div style="font-size: 2rem; font-weight: 800; color: #2D3748;">
            <?= $stats['open_cars'] ?? 0 ?>
        </div>
        <div style="font-size: 0.95rem; color: #718096; margin-top: 5px;">
            CAR باز
        </div>
    </div>
</div>

<!-- بخش دو ستونه -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    
    <!-- آخرین عدم انطباق‌ها -->
    <div class="card" style="padding: 25px;">
        <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <i class="fas fa-exclamation-triangle" style="color: #EF4444;"></i>
            آخرین عدم انطباق‌ها
        </h3>
        
        <?php if (empty($recentNcs)): ?>
            <p style="color: #718096; text-align: center; padding: 20px;">
                <i class="fas fa-check-circle" style="color: #10B981;"></i>
                عدم انطباقی ثبت نشده است
            </p>
        <?php else: ?>
            <?php foreach ($recentNcs as $nc): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f0f0f0;">
                    <div>
                        <strong style="color: #2D3748;"><?= qms_e($nc['nc_number']) ?></strong>
                        <div style="font-size: 0.85rem; color: #718096; margin-top: 4px;">
                            <?= qms_e($nc['title']) ?>
                        </div>
                        <?php if (!empty($nc['dept_name'])): ?>
                            <span style="font-size: 0.75rem; background: #E0E7FF; color: #4F46E5; padding: 2px 8px; border-radius: 10px;">
                                <?= qms_e($nc['dept_name']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span style="background: <?= qms_severity_color($nc['severity']) ?>20; color: <?= qms_severity_color($nc['severity']) ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        <?= qms_status_label($nc['severity']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- ممیزی‌های پیش رو -->
    <div class="card" style="padding: 25px;">
        <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">
            <i class="fas fa-calendar-alt" style="color: #3B82F6;"></i>
            ممیزی‌های پیش رو
        </h3>
        
        <?php if (empty($upcomingAudits)): ?>
            <p style="color: #718096; text-align: center; padding: 20px;">
                <i class="fas fa-info-circle"></i>
                ممیزی زمان‌بندی شده‌ای وجود ندارد
            </p>
        <?php else: ?>
            <?php foreach ($upcomingAudits as $audit): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f0f0f0;">
                    <div>
                        <strong style="color: #2D3748;"><?= qms_e($audit['title']) ?></strong>
                        <div style="font-size: 0.85rem; color: #718096; margin-top: 4px;">
                            <i class="fas fa-calendar"></i>
                            <?= qms_date_fa($audit['start_date']) ?>
                            تا
                            <?= qms_date_fa($audit['end_date']) ?>
                        </div>
                        <?php if (!empty($audit['lead_auditor_name'])): ?>
                            <div style="font-size: 0.8rem; color: #6C3CE1; margin-top: 2px;">
                                <i class="fas fa-user-check"></i>
                                <?= qms_e($audit['lead_auditor_name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <span style="background: #DBEAFE; color: #1E40AF; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        <?= qms_status_label($audit['status']) ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- دسترسی سریع -->
<div class="card" style="margin-top: 20px; padding: 25px;">
    <h3 style="margin: 0 0 20px 0; color: #2D3748;">
        <i class="fas fa-bolt" style="color: #F59E0B;"></i>
        دسترسی سریع
    </h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <a href="?controller=auditplans&action=create" class="btn" style="background: #3B82F6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> برنامه ممیزی جدید
        </a>
        <a href="?controller=nonconformities&action=create" class="btn" style="background: #EF4444; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-exclamation"></i> ثبت عدم انطباق
        </a>
        <a href="?controller=isoclauses" class="btn" style="background: #6C3CE1; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-book"></i> بندهای استاندارد
        </a>
        <a href="?controller=reports" class="btn" style="background: #10B981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-chart-bar"></i> گزارش‌ها
        </a>
        <a href="?controller=departments" class="btn" style="background: #F59E0B; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-building"></i> واحدها
        </a>
        <a href="?controller=auditors" class="btn" style="background: #8B5CF6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-user-check"></i> ممیزان
        </a>
    </div>
</div>