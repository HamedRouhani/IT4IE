<?php
$pageTitle = 'گزارش‌های ممیزی';
$currentPage = 'reports';

$statusColors = [
    'draft' => '#6B7280',
    'review' => '#F59E0B',
    'finalized' => '#3B82F6',
    'distributed' => '#10B981',
    'archived' => '#8B5CF6'
];
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-chart-bar" style="color: #6C3CE1;"></i>
            گزارش‌های ممیزی
        </h1>
        <a href="?controller=reports&action=dashboard" 
           style="background: #10B981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-tachometer-alt"></i> داشبورد مدیریت
        </a>
    </div>

    <!-- کارت‌های آمار -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #6C3CE1; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="font-size: 2rem; font-weight: 800; color: #6C3CE1;"><?= $stats['total_plans'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.85rem;">برنامه‌های ممیزی</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #10B981; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="font-size: 2rem; font-weight: 800; color: #10B981;"><?= $stats['completed_sessions'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.85rem;">جلسات تکمیل شده</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #EF4444; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="font-size: 2rem; font-weight: 800; color: #EF4444;"><?= $stats['open_ncs'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.85rem;">عدم انطباق باز</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #F59E0B; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="font-size: 2rem; font-weight: 800; color: #F59E0B;"><?= $stats['total_reports'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.85rem;">گزارش‌های تولید شده</div>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- تولید گزارش جدید -->
    <div style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
        <h3 style="margin: 0 0 15px 0;">
            <i class="fas fa-magic"></i> تولید گزارش نهایی
        </h3>
        <p style="opacity: 0.9; margin-bottom: 15px;">
            از بین برنامه‌های ممیزی تکمیل شده، یک گزارش نهایی برای مدیریت ارشد تولید کنید
        </p>
        <form method="POST" action="?controller=reports&action=generate" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <select name="audit_plan_id" required
                    style="flex: 1; min-width: 250px; padding: 10px; border: none; border-radius: 8px;">
                <option value="">-- انتخاب برنامه ممیزی تکمیل شده --</option>
                <?php
                $completedPlans = $this->db->query("
                    SELECT id, title FROM {$this->prefix}audit_plans 
                    WHERE status = 'completed' ORDER BY created_at DESC
                ")->fetchAll();
                foreach ($completedPlans as $plan):
                ?>
                    <option value="<?= $plan['id'] ?>"><?= qms_e($plan['title']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" 
                    style="background: white; color: #6C3CE1; padding: 10px 25px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-file-alt"></i> تولید گزارش
            </button>
        </form>
    </div>

    <!-- لیست گزارش‌ها -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 20px 0; color: #2D3748;">
            <i class="fas fa-list" style="color: #6C3CE1;"></i>
            گزارش‌های تولید شده (<?= count($reports) ?>)
        </h3>

        <?php if (empty($reports)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-file-alt" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 style="margin-top: 20px;">هنوز گزارشی تولید نشده است</h4>
                <p>با استفاده از فرم بالا، اولین گزارش را تولید کنید</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">شماره گزارش</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان برنامه</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تهیه‌کننده</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;">
                                    <strong style="color: #6C3CE1;"><?= qms_e($report['report_number']) ?></strong>
                                </td>
                                <td style="padding: 12px; color: #2D3748;">
                                    <?= qms_e($report['plan_title']) ?>
                                </td>
                                <td style="padding: 12px; color: #4A5568;">
                                    <?= qms_e($report['prepared_by_name'] ?? '-') ?>
                                </td>
                                <td style="padding: 12px; color: #4A5568;">
                                    <?= qms_e(date('Y/m/d', strtotime($report['created_at']))) ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php $color = $statusColors[$report['status']] ?? '#6B7280'; ?>
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($report['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="?controller=reports&action=show&id=<?= $report['id'] ?>" 
                                       style="background: #6C3CE1; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> مشاهده
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>