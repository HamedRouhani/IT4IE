<?php
$pageTitle = 'جزئیات بازنگری مدیریت';
$currentPage = 'managementreviews';

$statusColors = [
    'draft' => '#6B7280', 'scheduled' => '#3B82F6', 'completed' => '#10B981', 'archived' => '#8B5CF6'
];
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
                <i class="fas fa-users-cog" style="color: #6C3CE1;"></i>
                <?= qms_e($review['review_number']) ?>
            </h1>
            <p style="color: #718096; margin-top: 5px;"><?= qms_e($review['title']) ?></p>
        </div>
        <a href="?controller=managementreviews" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <!-- ورودی‌ها -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-right: 4px solid #3B82F6;">
                <h3 style="margin: 0 0 20px 0; color: #2D3748;"><i class="fas fa-arrow-down" style="color: #3B82F6;"></i> ورودی‌های بازنگری</h3>
                
                <?php
                $inputs = [
                    'previous_actions_status' => '۱. وضعیت اقدامات بازنگری‌های قبلی',
                    'changes_in_context' => '۲. تغییرات در مسائل خارجی و داخلی',
                    'performance_effectiveness' => '۳. عملکرد و اثربخشی QMS',
                    'resource_adequacy' => '۴. کفایت منابع',
                    'risk_effectiveness' => '۵. اثربخشی اقدامات ریسک و فرصت',
                    'improvement_opportunities_input' => '۶. فرصت‌های بهبود'
                ];
                foreach ($inputs as $key => $label):
                    if (!empty($review[$key])):
                ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #4A5568; font-size: 0.9rem;"><?= $label ?></strong>
                        <div style="background: #F7FAFC; padding: 12px; border-radius: 8px; margin-top: 5px; line-height: 1.7; color: #2D3748;">
                            <?= nl2br(qms_e($review[$key])) ?>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>

            <!-- خروجی‌ها -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-right: 4px solid #10B981;">
                <h3 style="margin: 0 0 20px 0; color: #2D3748;"><i class="fas fa-arrow-up" style="color: #10B981;"></i> خروجی‌ها و تصمیمات</h3>
                
                <?php
                $outputs = [
                    'improvement_actions' => '۱. اقدامات مربوط به فرصت‌های بهبود',
                    'qms_changes' => '۲. نیاز به تغییرات در QMS',
                    'resource_needs' => '۳. نیازمندی‌های منابع',
                    'decisions_made' => '۴. سایر تصمیمات کلیدی'
                ];
                foreach ($outputs as $key => $label):
                    if (!empty($review[$key])):
                ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #4A5568; font-size: 0.9rem;"><?= $label ?></strong>
                        <div style="background: #F0FDF4; padding: 12px; border-radius: 8px; margin-top: 5px; line-height: 1.7; color: #065F46;">
                            <?= nl2br(qms_e($review[$key])) ?>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>

        <!-- ستون کناری -->
        <div>
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;"><i class="fas fa-info-circle" style="color: #6C3CE1;"></i> اطلاعات جلسه</h4>
                <div style="font-size: 0.9rem; color: #4A5568; line-height: 2;">
                    <p><strong>تهیه‌کننده:</strong> <?= qms_e($review['created_by_name'] ?? '-') ?></p>
                    <p><strong>تاریخ برگزاری:</strong> <?= qms_e($review['review_date']) ?></p>
                    <p><strong>محل برگزاری:</strong> <?= qms_e($review['meeting_location'] ?? '-') ?></p>
                    <p><strong>وضعیت:</strong> 
                        <?php $color = $statusColors[$review['status']] ?? '#6B7280'; ?>
                        <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 3px 10px; border-radius: 10px; font-size: 0.8rem;">
                            <?= qms_status_label($review['status']) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>