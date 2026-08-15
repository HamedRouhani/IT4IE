<?php
/**
 * ویو مشاهده جزئیات برنامه ممیزی - ماژول QMS
 * مسیر: app/software/qms/views/audit-plans/view.php
 */
$pageTitle = htmlspecialchars($plan['title']);
$currentPage = 'auditplans';
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-clipboard-list" style="color: #6C3CE1;"></i>
            <?= htmlspecialchars($plan['title']) ?>
        </h1>
        <div style="display: flex; gap: 10px;">
            <!-- ✅ اصلاح شده: بازگشت به لیست ماژولار -->
            <a href="?controller=auditplans" class="btn" style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت به لیست
            </a>
            
            <?php if (($_SESSION['user_role'] ?? '') === 'admin' || $plan['user_id'] == ($_SESSION['user_id'] ?? 0)): ?>
                <!-- ✅ اصلاح شده: لینک ویرایش ماژولار -->
                <a href="?controller=auditplans&action=edit&id=<?= $plan['id'] ?>" class="btn" style="background: #F59E0B; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                    <i class="fas fa-edit"></i> ویرایش
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- پیام‌ها -->
    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <div>
            <!-- کارت اطلاعات برنامه -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-info-circle" style="color: #6C3CE1;"></i> اطلاعات برنامه
                </h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <p style="margin-bottom: 10px;"><strong>نوع ممیزی:</strong> <?= $plan['audit_type'] === 'internal' ? 'داخلی' : 'خارجی' ?></p>
                        <p style="margin-bottom: 10px;"><strong>سرممیز:</strong> <?= htmlspecialchars($plan['lead_auditor_name'] ?? '-') ?></p>
                        <p style="margin-bottom: 10px;"><strong>تاریخ شروع:</strong> <?= date('Y/m/d', strtotime($plan['start_date'])) ?></p>
                        <p style="margin-bottom: 10px;"><strong>تاریخ پایان:</strong> <?= date('Y/m/d', strtotime($plan['end_date'])) ?></p>
                    </div>
                    <div>
                        <p style="margin-bottom: 10px;">
                            <strong>وضعیت:</strong> 
                            <?php
                            $statusColors = [
                                'draft' => '#6B7280',
                                'scheduled' => '#3B82F6',
                                'in_progress' => '#F59E0B',
                                'completed' => '#10B981',
                                'cancelled' => '#EF4444'
                            ];
                            $statusLabels = [
                                'draft' => 'پیش‌نویس',
                                'scheduled' => 'زمان‌بندی شده',
                                'in_progress' => 'در حال انجام',
                                'completed' => 'تکمیل شده',
                                'cancelled' => 'لغو شده'
                            ];
                            $color = $statusColors[$plan['status']] ?? '#6B7280';
                            ?>
                            <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                <?= $statusLabels[$plan['status']] ?? $plan['status'] ?>
                            </span>
                        </p>
                        <p style="margin-bottom: 10px;"><strong>معیارها:</strong> <?= htmlspecialchars($plan['criteria'] ?? '-') ?></p>
                    </div>
                </div>

                <?php if (!empty($plan['scope'])): ?>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #E2E8F0;">
                    <h6 style="margin-bottom: 10px; color: #2D3748;"><i class="fas fa-bullseye" style="color: #6C3CE1;"></i> دامنه ممیزی</h6>
                    <p style="color: #4A5568; line-height: 1.7;"><?= nl2br(htmlspecialchars($plan['scope'])) ?></p>
                <?php endif; ?>

                <?php if (!empty($plan['objectives'])): ?>
                    <h6 style="margin-bottom: 10px; color: #2D3748;"><i class="fas fa-flag" style="color: #6C3CE1;"></i> اهداف ممیزی</h6>
                    <p style="color: #4A5568; line-height: 1.7;"><?= nl2br(htmlspecialchars($plan['objectives'])) ?></p>
                <?php endif; ?>
            </div>

            <!-- کارت آمار -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-chart-bar" style="color: #6C3CE1;"></i> آمار برنامه
                </h3>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; text-align: center;">
                    <div style="background: #EFF6FF; padding: 15px; border-radius: 8px;">
                        <h3 style="margin: 0; color: #3B82F6; font-size: 1.8rem;"><?= $statistics['sessions']['total'] ?? 0 ?></h3>
                        <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;">جلسات</p>
                    </div>
                    <div style="background: #F0FDF4; padding: 15px; border-radius: 8px;">
                        <h3 style="margin: 0; color: #10B981; font-size: 1.8rem;"><?= $statistics['sessions']['completed'] ?? 0 ?></h3>
                        <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;">تکمیل شده</p>
                    </div>
                    <div style="background: #F0F9FF; padding: 15px; border-radius: 8px;">
                        <h3 style="margin: 0; color: #0EA5E9; font-size: 1.8rem;"><?= $statistics['evidences']['total'] ?? 0 ?></h3>
                        <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;">شواهد</p>
                    </div>
                    <div style="background: #FEF2F2; padding: 15px; border-radius: 8px;">
                        <h3 style="margin: 0; color: #EF4444; font-size: 1.8rem;"><?= ($statistics['evidences']['minor_nc'] ?? 0) + ($statistics['evidences']['major_nc'] ?? 0) ?></h3>
                        <p style="margin: 5px 0 0 0; color: #4A5568; font-size: 0.9rem;">عدم انطباق</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ستون کناری -->
        <div>
            <!-- تغییر وضعیت -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-exchange-alt" style="color: #6C3CE1;"></i> تغییر وضعیت
                </h4>
                <!-- ✅ اصلاح شده: اکشن فرم ماژولار -->
                <form method="POST" action="?controller=auditplans&action=changeStatus&id=<?= $plan['id'] ?>">
                    <select name="status" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px; margin-bottom: 15px; font-family: inherit;">
                        <option value="draft" <?= $plan['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
                        <option value="scheduled" <?= $plan['status'] === 'scheduled' ? 'selected' : '' ?>>زمان‌بندی شده</option>
                        <option value="in_progress" <?= $plan['status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                        <option value="completed" <?= $plan['status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                        <option value="cancelled" <?= $plan['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                    </select>
                    <button type="submit" style="width: 100%; background: #6C3CE1; color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-family: inherit;">
                        <i class="fas fa-check"></i> اعمال تغییرات
                    </button>
                </form>
            </div>

            <!-- جلسات -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-calendar" style="color: #6C3CE1;"></i> جلسات
                </h4>
                <?php if (empty($sessions)): ?>
                    <p style="text-align: center; color: #718096; padding: 20px 0;">هنوز جلسه‌ای ثبت نشده است</p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($sessions as $session): ?>
                            <!-- ✅ اصلاح شده: لینک مشاهده جلسه ماژولار -->
                            <a href="?controller=auditsessions&action=show&id=<?= $session['id'] ?>" style="text-decoration: none; color: inherit; background: #F7FAFC; padding: 12px; border-radius: 8px; border: 1px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.borderColor='#6C3CE1'" onmouseout="this.style.borderColor='#E2E8F0'">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                    <h6 style="margin: 0; color: #2D3748; font-size: 0.95rem;"><?= htmlspecialchars($session['department_name'] ?? 'نامشخص') ?></h6>
                                    <small style="color: #718096;"><?= date('Y/m/d', strtotime($session['actual_date'] ?? $session['audit_date'])) ?></small>
                                </div>
                                <p style="margin: 0 0 5px 0; font-size: 0.85rem; color: #4A5568;"><?= htmlspecialchars($session['process_name'] ?? '-') ?></p>
                                <small style="color: #6C3CE1; font-size: 0.8rem;"><i class="fas fa-user"></i> <?= htmlspecialchars($session['auditor_name'] ?? '-') ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 992px) {
    .container-fluid > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>