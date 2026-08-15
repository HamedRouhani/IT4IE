<?php
$pageTitle = 'جلسه ممیزی - ' . ($session['department_name'] ?? '');
$currentPage = 'auditsessions';

$findingTypeColors = [
    'conformity' => '#10B981',
    'observation' => '#3B82F6',
    'minor_nc' => '#F59E0B',
    'major_nc' => '#EF4444',
    'ofI' => '#8B5CF6'
];

$findingTypeLabels = [
    'conformity' => 'انطباق',
    'observation' => 'مشاهده',
    'minor_nc' => 'عدم انطباق جزئی',
    'major_nc' => 'عدم انطباق عمده',
    'ofI' => 'فرصت بهبود'
];
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
                <i class="fas fa-clipboard-check" style="color: #6C3CE1;"></i>
                جلسه ممیزی: <?= qms_e($session['department_name'] ?? 'نامشخص') ?>
            </h1>
            <p style="color: #718096; margin-top: 5px;">
                <?= qms_e($session['plan_title'] ?? '') ?>
            </p>
        </div>
        <a href="?controller=auditplans&action=show&id=<?= $session['audit_plan_id'] ?>" 
           class="btn" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به برنامه
        </a>
    </div>

    <!-- پیام‌ها -->
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

    <!-- کارت‌های آمار -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #6C3CE1;">
            <div style="font-size: 2rem; font-weight: 800; color: #6C3CE1;"><?= $stats['total'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.9rem;">کل شواهد</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #10B981;">
            <div style="font-size: 2rem; font-weight: 800; color: #10B981;"><?= $stats['conformities'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.9rem;">انطباق</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #3B82F6;">
            <div style="font-size: 2rem; font-weight: 800; color: #3B82F6;"><?= $stats['observations'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.9rem;">مشاهدات</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #F59E0B;">
            <div style="font-size: 2rem; font-weight: 800; color: #F59E0B;"><?= $stats['minor_nc'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.9rem;">عدم انطباق جزئی</div>
        </div>
        <div style="background: white; border-radius: 10px; padding: 20px; text-align: center; border-right: 4px solid #EF4444;">
            <div style="font-size: 2rem; font-weight: 800; color: #EF4444;"><?= $stats['major_nc'] ?? 0 ?></div>
            <div style="color: #718096; font-size: 0.9rem;">عدم انطباق عمده</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <!-- ستون چپ: شواهد -->
        <div>
            <!-- فرم ثبت شاهد جدید -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                    <i class="fas fa-plus-circle" style="color: #6C3CE1;"></i>
                    ثبت شاهد جدید
                </h3>
                
                <form method="POST" action="?controller=auditsessions&action=addEvidence">
                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                بند استاندارد *
                            </label>
                            <select name="clause_id" required 
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="">-- انتخاب کنید --</option>
                                <?php foreach ($clauses as $clause): ?>
                                    <option value="<?= $clause['id'] ?>">
                                        <?= qms_e($clause['clause_number']) ?> - <?= qms_e($clause['title_fa']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                نوع یافته *
                            </label>
                            <select name="finding_type" required
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="conformity">✅ انطباق</option>
                                <option value="observation">🔵 مشاهده</option>
                                <option value="minor_nc">🟡 عدم انطباق جزئی</option>
                                <option value="major_nc">🔴 عدم انطباق عمده</option>
                                <option value="ofI">🟣 فرصت بهبود</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                            عنوان شاهد *
                        </label>
                        <input type="text" name="title" required
                               style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                            توضیحات کامل *
                        </label>
                        <textarea name="description" rows="3" required
                                  style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                نوع مدرک
                            </label>
                            <select name="evidence_type"
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="observation">مشاهده</option>
                                <option value="document">سند</option>
                                <option value="record">سابقه</option>
                                <option value="interview">مصاحبه</option>
                                <option value="photo">تصویر</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                شدت
                            </label>
                            <select name="severity"
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="low">کم</option>
                                <option value="medium">متوسط</option>
                                <option value="high">بالا</option>
                                <option value="critical">بحرانی</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" 
                            style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <i class="fas fa-save"></i> ثبت شاهد
                    </button>
                </form>
            </div>

            <!-- لیست شواهد ثبت شده -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                    <i class="fas fa-list" style="color: #6C3CE1;"></i>
                    شواهد ثبت شده (<?= count($evidences) ?>)
                </h3>

                <?php if (empty($evidences)): ?>
                    <p style="text-align: center; color: #718096; padding: 30px;">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <br>هنوز شاهدی ثبت نشده است
                    </p>
                <?php else: ?>
                    <?php foreach ($evidences as $ev): ?>
                        <div style="border: 1px solid #E2E8F0; border-radius: 10px; padding: 15px; margin-bottom: 12px; border-right: 4px solid <?= $findingTypeColors[$ev['finding_type']] ?? '#6B7280' ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                <div>
                                    <strong style="color: #2D3748; font-size: 1.05rem;">
                                        <?= qms_e($ev['title']) ?>
                                    </strong>
                                    <div style="font-size: 0.85rem; color: #718096; margin-top: 4px;">
                                        <span style="background: #F3F4F6; padding: 2px 8px; border-radius: 10px;">
                                            <?= qms_e($ev['clause_number'] ?? '') ?> - <?= qms_e($ev['clause_title'] ?? '') ?>
                                        </span>
                                    </div>
                                </div>
                                <span style="background: <?= $findingTypeColors[$ev['finding_type']] ?>20; color: <?= $findingTypeColors[$ev['finding_type']] ?>; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                    <?= $findingTypeLabels[$ev['finding_type']] ?? $ev['finding_type'] ?>
                                </span>
                            </div>
                            <p style="color: #4A5568; line-height: 1.7; margin: 0; font-size: 0.95rem;">
                                <?= nl2br(qms_e($ev['description'])) ?>
                            </p>
                            <?php if (!empty($ev['notes'])): ?>
                                <div style="margin-top: 10px; padding: 8px; background: #F7FAFC; border-radius: 6px; font-size: 0.85rem; color: #718096;">
                                    <i class="fas fa-sticky-note"></i> <?= qms_e($ev['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ستون راست: اطلاعات جلسه -->
        <div>
            <!-- اطلاعات جلسه -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-info-circle" style="color: #6C3CE1;"></i>
                    اطلاعات جلسه
                </h4>
                <div style="font-size: 0.9rem; color: #4A5568;">
                    <p><strong>واحد:</strong> <?= qms_e($session['department_name'] ?? '-') ?></p>
                    <p><strong>فرآیند:</strong> <?= qms_e($session['process_name'] ?? '-') ?></p>
                    <p><strong>تاریخ برنامه‌ریزی:</strong> <?= qms_e($session['audit_date'] ?? '-') ?></p>
                    <p><strong>ممیز:</strong> <?= qms_e($session['auditor_name'] ?? '-') ?></p>
                    <p><strong>وضعیت:</strong> 
                        <span style="background: #DBEAFE; color: #1E40AF; padding: 3px 10px; border-radius: 10px; font-size: 0.8rem;">
                            <?= qms_status_label($session['overall_status']) ?>
                        </span>
                    </p>
                </div>

                <form method="POST" action="?controller=auditsessions&action=updateStatus" style="margin-top: 15px;">
                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                    <select name="overall_status" style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px; margin-bottom: 10px;">
                        <option value="not_started" <?= $session['overall_status'] === 'not_started' ? 'selected' : '' ?>>شروع نشده</option>
                        <option value="in_progress" <?= $session['overall_status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                        <option value="completed" <?= $session['overall_status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                        <option value="postponed" <?= $session['overall_status'] === 'postponed' ? 'selected' : '' ?>>به تعویق افتاده</option>
                    </select>
                    <button type="submit" style="width: 100%; background: #6C3CE1; color: white; padding: 8px; border: none; border-radius: 8px; cursor: pointer;">
                        <i class="fas fa-check"></i> به‌روزرسانی وضعیت
                    </button>
                </form>

                <!-- دکمه ویرایش جلسه -->
                <a href="?controller=auditsessions&action=edit&id=<?= $session['id'] ?>" 
                style="display: block; background: #F59E0B; color: white; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; margin-top: 10px; font-weight: 600;">
                    <i class="fas fa-edit"></i> ویرایش اطلاعات جلسه
                </a>
            </div>

            <!-- دکمه افزودن ممیزی‌شونده -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-user-plus" style="color: #6C3CE1;"></i>
                    افزودن ممیزی‌شونده
                </h4>
                
                <form method="POST" action="?controller=auditsessions&action=addAuditee">
                    <input type="hidden" name="plan_item_id" value="<?= $session['plan_item_id'] ?>">
                    <input type="hidden" name="session_id" value="<?= $session['id'] ?>">
                    
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="full_name" placeholder="نام و نام خانوادگی *" required
                            style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <input type="text" name="position" placeholder="سمت (اختیاری)"
                            style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                    </div>
                    
                    <button type="submit" style="width: 100%; background: #10B981; color: white; padding: 8px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-plus"></i> افزودن ممیزی‌شونده
                    </button>
                </form>
            </div>

            <!-- ممیزی‌شوندگان -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-users" style="color: #6C3CE1;"></i>
                    ممیزی‌شوندگان (<?= count($auditees) ?>)
                </h4>

                <?php if (empty($auditees)): ?>
                    <p style="text-align: center; color: #718096; font-size: 0.9rem;">
                        ممیزی‌شونده‌ای ثبت نشده است
                    </p>
                <?php else: ?>
                    <?php foreach ($auditees as $auditee): ?>
                        <div style="padding: 10px; border-bottom: 1px solid #F0F0F0;">
                            <strong style="color: #2D3748;"><?= qms_e($auditee['full_name']) ?></strong>
                            <?php if (!empty($auditee['position'])): ?>
                                <div style="font-size: 0.85rem; color: #718096;"><?= qms_e($auditee['position']) ?></div>
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
    .container-fluid > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>