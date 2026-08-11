<?php
$pageTitle = 'CAR: ' . $car['car_number'];
$currentPage = 'car';

$statusColors = [
    'draft' => '#6B7280',
    'submitted' => '#3B82F6',
    'approved' => '#8B5CF6',
    'in_progress' => '#F59E0B',
    'implemented' => '#06B6D4',
    'verified' => '#10B981',
    'closed' => '#10B981',
    'rejected' => '#EF4444'
];

$actionTypeLabels = [
    'immediate' => 'اقدام فوری',
    'corrective' => 'اقدام اصلاحی',
    'preventive' => 'اقدام پیشگیرانه',
    'verification' => 'تأیید'
];

$actionTypeColors = [
    'immediate' => '#EF4444',
    'corrective' => '#F59E0B',
    'preventive' => '#3B82F6',
    'verification' => '#10B981'
];
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
                <i class="fas fa-clipboard-check" style="color: #10B981;"></i>
                CAR: <?= qms_e($car['car_number']) ?>
            </h1>
            <p style="color: #718096; margin-top: 5px;">
                مرتبط با NC: <?= qms_e($car['nc_number']) ?> - <?= qms_e($car['nc_title']) ?>
            </p>
        </div>
        <a href="?controller=car" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= qms_e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        
        <!-- ستون راست: اطلاعات CAR -->
        <div>
            <!-- وضعیت کلی -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-info-circle" style="color: #10B981;"></i>
                    وضعیت CAR
                </h4>
                
                <?php $sColor = $statusColors[$car['status']] ?? '#6B7280'; ?>
                <div style="background: <?= $sColor ?>20; color: <?= $sColor ?>; padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 15px;">
                    <div style="font-size: 1.3rem; font-weight: 700;">
                        <?= qms_status_label($car['status']) ?>
                    </div>
                </div>

                <!-- Progress Bar کلی -->
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span style="font-size: 0.85rem; color: #718096;">پیشرفت کلی:</span>
                        <span style="font-size: 0.85rem; font-weight: 600; color: #2D3748;"><?= $overallProgress ?>%</span>
                    </div>
                    <div style="background: #E5E7EB; border-radius: 10px; height: 10px; overflow: hidden;">
                        <div style="background: linear-gradient(90deg, #10B981, #34D399); height: 100%; width: <?= $overallProgress ?>%; transition: width 0.5s;"></div>
                    </div>
                </div>

                <div style="font-size: 0.9rem; color: #4A5568;">
                    <p><strong>ثبت‌کننده:</strong> <?= qms_e($car['created_by_name'] ?? '-') ?></p>
                    <p><strong>تاریخ ایجاد:</strong> <?= qms_e($car['created_at'] ?? '-') ?></p>
                    <p><strong>بند استاندارد:</strong> <?= qms_e($car['clause_number'] ?? '-') ?></p>
                </div>
            </div>

            <!-- تحلیل ریشه‌ای -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-search" style="color: #F59E0B;"></i>
                    تحلیل ریشه‌ای
                </h4>
                <?php if (!empty($car['root_cause_analysis'])): ?>
                    <div style="background: #FEF3C7; padding: 15px; border-radius: 8px; border-right: 4px solid #F59E0B;">
                        <?= nl2br(qms_e($car['root_cause_analysis'])) ?>
                    </div>
                <?php else: ?>
                    <p style="color: #718096; text-align: center;">تحلیل ریشه‌ای ثبت نشده است</p>
                <?php endif; ?>
            </div>

            <!-- تأیید اثربخشی -->
            <?php if ($car['status'] === 'implemented'): ?>
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                        <i class="fas fa-check-double" style="color: #10B981;"></i>
                        تأیید اثربخشی (توسط ممیز)
                    </h4>
                    
                    <form method="POST" action="?controller=car&action=verifyEffectiveness">
                        <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                                آیا اقدامات مؤثر بودند؟
                            </label>
                            <select name="is_effective" required
                                    style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="1">✅ بله، مؤثر بودند</option>
                                <option value="0">❌ خیر، نیاز به اقدام مجدد</option>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                                توضیحات اثربخشی *
                            </label>
                            <textarea name="effectiveness_check" rows="3" required
                                      style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                        </div>

                        <button type="submit" 
                                style="width: 100%; background: #10B981; color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-check"></i> ثبت تأیید
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- ستون چپ: اقدامات و تسک‌ها -->
        <div>
            <!-- افزودن اقدام جدید -->
            <?php if ($car['status'] !== 'verified' && $car['status'] !== 'closed'): ?>
                <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                        <i class="fas fa-plus-circle" style="color: #10B981;"></i>
                        افزودن اقدام جدید
                    </h4>
                    
                    <form method="POST" action="?controller=car&action=addAction">
                        <input type="hidden" name="car_form_id" value="<?= $car['id'] ?>">
                        
                        <div style="margin-bottom: 12px;">
                            <input type="text" name="action_title" placeholder="عنوان اقدام *" required
                                   style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                        </div>

                        <div style="margin-bottom: 12px;">
                            <textarea name="action_description" rows="2" placeholder="توضیحات اقدام *" required
                                      style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                            <select name="action_type" style="padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="corrective">اقدام اصلاحی</option>
                                <option value="immediate">اقدام فوری</option>
                                <option value="preventive">اقدام پیشگیرانه</option>
                                <option value="verification">تأیید</option>
                            </select>

                            <select name="priority" style="padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="low">اولویت کم</option>
                                <option value="medium" selected>اولویت متوسط</option>
                                <option value="high">اولویت بالا</option>
                                <option value="critical">بحرانی</option>
                            </select>

                            <input type="date" name="due_date" 
                                   value="<?= date('Y-m-d', strtotime('+14 days')) ?>"
                                   style="padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                        </div>

                        <div style="margin-bottom: 12px;">
                            <select name="responsible_person_id" style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= $user['id'] == $this->currentUserId ? 'selected' : '' ?>>
                                        <?= qms_e($user['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" 
                                style="width: 100%; background: #10B981; color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                            <i class="fas fa-plus"></i> افزودن اقدام
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- لیست اقدامات -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 20px 0; color: #2D3748;">
                    <i class="fas fa-tasks" style="color: #10B981;"></i>
                    اقدامات (<?= count($actions) ?>)
                </h4>

                <?php if (empty($actions)): ?>
                    <p style="text-align: center; color: #718096; padding: 30px;">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <br>هنوز اقدامی ثبت نشده است
                    </p>
                <?php else: ?>
                    <?php foreach ($actions as $action): ?>
                        <div style="border: 1px solid #E2E8F0; border-radius: 10px; padding: 15px; margin-bottom: 15px;">
                            <!-- هدر اقدام -->
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                                        <span style="background: <?= $actionTypeColors[$action['action_type']] ?? '#6B7280' ?>; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">
                                            <?= $actionTypeLabels[$action['action_type']] ?? $action['action_type'] ?>
                                        </span>
                                        <strong style="color: #2D3748;">اقدام <?= $action['action_number'] ?></strong>
                                    </div>
                                    <h5 style="margin: 0; color: #2D3748;"><?= qms_e($action['action_title']) ?></h5>
                                </div>
                                <div style="text-align: left;">
                                    <span style="background: #F3F4F6; padding: 3px 8px; border-radius: 10px; font-size: 0.75rem;">
                                        <?= $action['completed_tasks'] ?>/<?= $action['total_tasks'] ?> تسک
                                    </span>
                                </div>
                            </div>

                            <!-- توضیحات -->
                            <p style="color: #4A5568; font-size: 0.9rem; margin: 10px 0; line-height: 1.6;">
                                <?= nl2br(qms_e($action['action_description'])) ?>
                            </p>

                            <!-- اطلاعات تکمیلی -->
                            <div style="display: flex; gap: 15px; font-size: 0.8rem; color: #718096; margin-bottom: 10px;">
                                <span><i class="fas fa-user"></i> <?= qms_e($action['responsible_name'] ?? '-') ?></span>
                                <span><i class="fas fa-calendar"></i> <?= qms_e($action['due_date'] ?? '-') ?></span>
                            </div>

                            <!-- Progress Bar اقدام -->
                            <?php $actionProgress = $action['total_tasks'] > 0 ? round(($action['completed_tasks'] / $action['total_tasks']) * 100) : 0; ?>
                            <div style="margin-bottom: 10px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                                    <span style="font-size: 0.75rem; color: #718096;">پیشرفت:</span>
                                    <span style="font-size: 0.75rem; font-weight: 600;"><?= $actionProgress ?>%</span>
                                </div>
                                <div style="background: #E5E7EB; border-radius: 5px; height: 6px; overflow: hidden;">
                                    <div style="background: #10B981; height: 100%; width: <?= $actionProgress ?>%;"></div>
                                </div>
                            </div>

                            <!-- افزودن تسک -->
                            <?php if ($car['status'] !== 'verified' && $car['status'] !== 'closed'): ?>
                                <form method="POST" action="?controller=car&action=addTask" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #E2E8F0;">
                                    <input type="hidden" name="action_id" value="<?= $action['id'] ?>">
                                    <input type="hidden" name="car_id" value="<?= $car['id'] ?>">
                                    
                                    <div style="display: flex; gap: 8px;">
                                        <input type="text" name="task_title" placeholder="عنوان تسک جدید..." required
                                               style="flex: 1; padding: 8px; border: 2px solid #E2E8F0; border-radius: 6px; font-size: 0.85rem;">
                                        <button type="submit" 
                                                style="background: #3B82F6; color: white; padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 992px) {
    .container-fluid > div[style*="grid-template-columns: 1fr 2fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>