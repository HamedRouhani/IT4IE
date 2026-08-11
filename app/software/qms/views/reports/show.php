<?php
$pageTitle = 'گزارش: ' . $report['report_number'];
$currentPage = 'reports';

$findingTypeLabels = [
    'conformity' => 'انطباق',
    'observation' => 'مشاهده',
    'minor_nc' => 'عدم انطباق جزئی',
    'major_nc' => 'عدم انطباق عمده',
    'ofI' => 'فرصت بهبود'
];

$findingTypeColors = [
    'conformity' => '#10B981',
    'observation' => '#3B82F6',
    'minor_nc' => '#F59E0B',
    'major_nc' => '#EF4444',
    'ofI' => '#8B5CF6'
];

$statusColors = [
    'draft' => '#6B7280',
    'review' => '#F59E0B',
    'finalized' => '#3B82F6',
    'distributed' => '#10B981',
    'archived' => '#8B5CF6'
];

$maturityLabels = [
    'initial' => 'مقدماتی',
    'managed' => 'مدیریت شده',
    'defined' => 'تعریف شده',
    'quantitatively_managed' => 'مدیریت کمی',
    'optimizing' => 'بهینه‌سازی'
];
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر گزارش -->
    <div style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; border-radius: 12px; padding: 30px; margin-bottom: 25px;">
        <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 15px;">
            <div>
                <div style="font-size: 0.9rem; opacity: 0.8; margin-bottom: 5px;">
                    <i class="fas fa-file-alt"></i> گزارش نهایی ممیزی
                </div>
                <h1 style="margin: 0; font-size: 2rem;"><?= qms_e($report['report_number']) ?></h1>
                <p style="margin: 10px 0 0 0; opacity: 0.9;"><?= qms_e($report['plan_title']) ?></p>
            </div>
            <div style="text-align: left;">
                <?php $sColor = $statusColors[$report['status']] ?? '#6B7280'; ?>
                <span style="background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; font-weight: 600;">
                    <?= qms_status_label($report['status']) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- دکمه‌های عملیات -->
    <div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
        <a href="?controller=reports" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
        
        <?php if ($report['status'] !== 'distributed'): ?>
            <form method="POST" action="?controller=reports&action=updateStatus" style="display: inline;">
                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                <input type="hidden" name="status" value="distributed">
                <button type="submit" 
                        style="background: #10B981; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
                    <i class="fas fa-share"></i> توزیع به مدیریت
                </button>
            </form>
        <?php endif; ?>

        <button onclick="window.print()" 
                style="background: #3B82F6; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-print"></i> چاپ گزارش
        </button>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <!-- ستون چپ: محتوای گزارش -->
        <div>
            <!-- خلاصه مدیریتی -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #6C3CE1; padding-bottom: 10px;">
                    <i class="fas fa-briefcase" style="color: #6C3CE1;"></i>
                    خلاصه مدیریتی
                </h3>
                <div style="background: #F7FAFC; padding: 20px; border-radius: 8px; line-height: 1.8; color: #4A5568;">
                    <?= nl2br(qms_e($report['executive_summary'] ?? 'خلاصه‌ای ثبت نشده است.')) ?>
                </div>
            </div>

            <!-- نتیجه‌گیری کلی -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #10B981; padding-bottom: 10px;">
                    <i class="fas fa-check-double" style="color: #10B981;"></i>
                    نتیجه‌گیری کلی
                </h3>
                <div style="background: #D1FAE5; padding: 20px; border-radius: 8px; line-height: 1.8; color: #065F46;">
                    <?= nl2br(qms_e($report['overall_conclusion'] ?? 'نتیجه‌گیری ثبت نشده است.')) ?>
                </div>
            </div>

            <!-- عدم انطباق‌ها -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #EF4444; padding-bottom: 10px;">
                    <i class="fas fa-exclamation-triangle" style="color: #EF4444;"></i>
                    عدم انطباق‌های شناسایی شده (<?= count($ncs) ?>)
                </h3>

                <?php if (empty($ncs)): ?>
                    <p style="text-align: center; color: #10B981; padding: 30px;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; display: block; margin-bottom: 10px;"></i>
                        هیچ عدم انطباقی شناسایی نشد
                    </p>
                <?php else: ?>
                    <?php foreach ($ncs as $nc): ?>
                        <div style="border: 1px solid #E2E8F0; border-radius: 10px; padding: 15px; margin-bottom: 12px; border-right: 4px solid <?= $nc['severity'] === 'critical' ? '#EF4444' : ($nc['severity'] === 'major' ? '#F97316' : '#F59E0B') ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <div>
                                    <strong style="color: #2D3748;"><?= qms_e($nc['nc_number']) ?></strong>
                                    <span style="background: #F3F4F6; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-right: 8px;">
                                        <?= qms_e($nc['clause_number'] ?? '') ?>
                                    </span>
                                </div>
                                <span style="background: <?= $nc['severity'] === 'critical' ? '#FEE2E2' : ($nc['severity'] === 'major' ? '#FFEDD5' : '#FEF3C7') ?>; color: <?= $nc['severity'] === 'critical' ? '#DC2626' : ($nc['severity'] === 'major' ? '#EA580C' : '#D97706') ?>; padding: 3px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                    <?= qms_e($nc['severity']) ?>
                                </span>
                            </div>
                            <h5 style="margin: 0 0 8px 0; color: #2D3748;"><?= qms_e($nc['title']) ?></h5>
                            <p style="margin: 0; color: #4A5568; font-size: 0.9rem; line-height: 1.6;">
                                <?= nl2br(qms_e($nc['description'])) ?>
                            </p>
                            <?php if (!empty($nc['car_number'])): ?>
                                <div style="margin-top: 10px; font-size: 0.85rem; color: #6C3CE1;">
                                    <i class="fas fa-clipboard-check"></i> CAR: <?= qms_e($nc['car_number']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- توصیه‌ها -->
            <?php if (!empty($report['recommendations'])): ?>
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #F59E0B; padding-bottom: 10px;">
                        <i class="fas fa-lightbulb" style="color: #F59E0B;"></i>
                        توصیه‌ها
                    </h3>
                    <div style="background: #FEF3C7; padding: 20px; border-radius: 8px; line-height: 1.8; color: #78350F;">
                        <?= nl2br(qms_e($report['recommendations'])) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ستون راست: اطلاعات و آمار -->
        <div>
            <!-- اطلاعات گزارش -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-info-circle" style="color: #6C3CE1;"></i>
                    اطلاعات گزارش
                </h4>
                <div style="font-size: 0.9rem; color: #4A5568;">
                    <p style="margin: 8px 0;"><strong>تهیه‌کننده:</strong> <?= qms_e($report['prepared_by_name'] ?? '-') ?></p>
                    <p style="margin: 8px 0;"><strong>سرممیز:</strong> <?= qms_e($report['lead_auditor_name'] ?? '-') ?></p>
                    <p style="margin: 8px 0;"><strong>تاریخ شروع:</strong> <?= qms_e($report['start_date'] ?? '-') ?></p>
                    <p style="margin: 8px 0;"><strong>تاریخ پایان:</strong> <?= qms_e($report['end_date'] ?? '-') ?></p>
                    <p style="margin: 8px 0;"><strong>نوع ممیزی:</strong> <?= qms_e($report['audit_type'] ?? '-') ?></p>
                    <?php if (!empty($report['maturity_level'])): ?>
                        <p style="margin: 8px 0;"><strong>سطح بلوغ:</strong> 
                            <span style="background: #6C3CE1; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">
                                <?= $maturityLabels[$report['maturity_level']] ?? $report['maturity_level'] ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- آمار یافته‌ها -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-chart-pie" style="color: #6C3CE1;"></i>
                    آمار یافته‌ها
                </h4>
                
                <?php foreach ($findingTypeLabels as $key => $label): ?>
                    <?php $count = $detailedStats[$key . 's'] ?? $detailedStats[$key] ?? 0; ?>
                    <?php if ($count > 0): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #F0F0F0;">
                            <span style="color: #4A5568; font-size: 0.9rem;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: <?= $findingTypeColors[$key] ?>; margin-left: 8px;"></span>
                                <?= $label ?>
                            </span>
                            <strong style="color: <?= $findingTypeColors[$key] ?>;"><?= $count ?></strong>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

                <div style="margin-top: 15px; padding-top: 15px; border-top: 2px solid #E2E8F0; text-align: center;">
                    <div style="font-size: 0.85rem; color: #718096;">مجموع شواهد</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #6C3CE1;"><?= $detailedStats['total_evidences'] ?? 0 ?></div>
                </div>
            </div>

            <!-- دامنه و معیارها -->
            <?php if (!empty($report['scope']) || !empty($report['criteria'])): ?>
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                        <i class="fas fa-bullseye" style="color: #6C3CE1;"></i>
                        دامنه و معیارها
                    </h4>
                    <?php if (!empty($report['scope'])): ?>
                        <div style="margin-bottom: 15px;">
                            <strong style="font-size: 0.85rem; color: #718096;">دامنه:</strong>
                            <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;"><?= qms_e($report['scope']) ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($report['criteria'])): ?>
                        <div>
                            <strong style="font-size: 0.85rem; color: #718096;">معیارها:</strong>
                            <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;"><?= qms_e($report['criteria']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
@media print {
    .container-fluid > div:first-child,
    .container-fluid > div:nth-child(2) {
        display: none !important;
    }
    body {
        background: white !important;
    }
}
@media (max-width: 992px) {
    .container-fluid > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>