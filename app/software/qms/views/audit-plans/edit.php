<?php
/**
 * ویو ویرایش برنامه ممیزی - ماژول QMS
 * مسیر: app/software/qms/views/audit-plans/edit.php
 */
$pageTitle = 'ویرایش برنامه ممیزی';
$currentPage = 'auditplans';

// تبدیل تاریخ میلادی به شمسی برای نمایش در فیلد
$startDateShamsi = function_exists('jdate') ? jdate('Y/m/d', strtotime($plan['start_date'])) : $plan['start_date'];
$endDateShamsi = function_exists('jdate') ? jdate('Y/m/d', strtotime($plan['end_date'])) : $plan['end_date'];

// تبدیل رشته JSON واحدها به آرایه برای بررسی چک‌باکس‌ها
$planDepartments = json_decode($plan['departments'] ?? '[]', true);
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-edit" style="color: #F59E0B;"></i>
            ویرایش برنامه ممیزی
        </h1>
        <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" class="btn" style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px;">
        <!-- ✅ اکشن فرم به متد update ارسال می‌شود -->
        <form method="POST" action="?controller=auditplans&action=update&id=<?= $plan['id'] ?>" id="auditPlanEditForm">
            
            <!-- ردیف ۱: عنوان و نوع ممیزی -->
            <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 300px;">
                    <div class="form-group">
                        <label for="title" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            عنوان برنامه ممیزی <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               value="<?= htmlspecialchars($plan['title']) ?>" 
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <div class="form-group">
                        <label for="audit_type" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            نوع ممیزی
                        </label>
                        <select class="form-control" 
                                id="audit_type" 
                                name="audit_type"
                                style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                            <option value="internal" <?= $plan['audit_type'] === 'internal' ? 'selected' : '' ?>>داخلی</option>
                            <option value="external" <?= $plan['audit_type'] === 'external' ? 'selected' : '' ?>>خارجی</option>
                            <option value="surveillance" <?= $plan['audit_type'] === 'surveillance' ? 'selected' : '' ?>>نظارتی</option>
                            <option value="recertification" <?= $plan['audit_type'] === 'recertification' ? 'selected' : '' ?>>تمدید گواهینامه</option>
                            <option value="special" <?= $plan['audit_type'] === 'special' ? 'selected' : '' ?>>ویژه</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ردیف ۲: تاریخ شروع و پایان (شمسی) -->
            <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <div class="form-group">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            تاریخ شروع <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control persian-date-input" 
                               data-target="start_date_gregorian" 
                               placeholder="مثال: ۱۴۰۵/۰۵/۲۰" 
                               autocomplete="off" 
                               value="<?= $startDateShamsi ?>"
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                        
                        <input type="hidden" 
                               name="start_date" 
                               id="start_date_gregorian" 
                               value="<?= htmlspecialchars($plan['start_date']) ?>">
                        
                        <small style="color: #718096; font-size: 0.8rem; margin-top: 4px; display: block;">
                            میلادی: <span id="start_date_preview"><?= htmlspecialchars($plan['start_date']) ?></span>
                        </small>
                    </div>
                </div>
                <div style="flex: 1; min-width: 250px;">
                    <div class="form-group">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            تاریخ پایان <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control persian-date-input" 
                               data-target="end_date_gregorian" 
                               placeholder="مثال: ۱۴۰۵/۰۵/۲۵" 
                               autocomplete="off" 
                               value="<?= $endDateShamsi ?>"
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                        
                        <input type="hidden" 
                               name="end_date" 
                               id="end_date_gregorian" 
                               value="<?= htmlspecialchars($plan['end_date']) ?>">
                        
                        <small style="color: #718096; font-size: 0.8rem; margin-top: 4px; display: block;">
                            میلادی: <span id="end_date_preview"><?= htmlspecialchars($plan['end_date']) ?></span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- سرممیز -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="lead_auditor_id" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    سرممیز <span style="color: #EF4444;">*</span>
                </label>
                <select class="form-control" 
                        id="lead_auditor_id" 
                        name="lead_auditor_id" 
                        required
                        style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    <option value="">-- انتخاب کنید --</option>
                    <?php if (!empty($auditors)): ?>
                        <?php foreach ($auditors as $auditor): ?>
                            <option value="<?= $auditor['id'] ?>" 
                                    <?= $plan['lead_auditor_id'] == $auditor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($auditor['full_name']) ?>
                                <?= !empty($auditor['lead_auditor']) ? '(سرممیز)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>هیچ ممیزی ثبت نشده است</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- دامنه ممیزی -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="scope" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    دامنه ممیزی
                </label>
                <textarea class="form-control" 
                          id="scope" 
                          name="scope" 
                          rows="3"
                          style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($plan['scope'] ?? '') ?></textarea>
            </div>

            <!-- اهداف ممیزی -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="objectives" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    اهداف ممیزی
                </label>
                <textarea class="form-control" 
                          id="objectives" 
                          name="objectives" 
                          rows="3"
                          style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($plan['objectives'] ?? '') ?></textarea>
            </div>

            <!-- معیارهای ممیزی -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="criteria" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    معیارهای ممیزی
                </label>
                <input type="text" 
                       class="form-control" 
                       id="criteria" 
                       name="criteria" 
                       value="<?= htmlspecialchars($plan['criteria'] ?? 'ISO 9001:2015') ?>"
                       style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
            </div>

            <!-- واحدهای تحت ممیزی -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 12px; color: #4A5568;">
                    واحدهای تحت ممیزی
                </label>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                            <div style="flex: 1; min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 8px; padding: 10px; background: #F7FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
                                    <input type="checkbox" 
                                           id="dept_<?= $dept['id'] ?>" 
                                           name="departments[]" 
                                           value="<?= $dept['id'] ?>"
                                           <?= in_array($dept['id'], $planDepartments) ? 'checked' : '' ?>
                                           style="width: 18px; height: 18px; cursor: pointer;">
                                    <label for="dept_<?= $dept['id'] ?>" 
                                           style="cursor: pointer; margin: 0; color: #2D3748;">
                                        <?= htmlspecialchars($dept['name_fa']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #718096; margin: 0;">
                            <i class="fas fa-info-circle"></i> 
                            هیچ واحد سازمانی ثبت نشده است.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- دکمه‌های عملیات -->
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #E2E8F0; display: flex; gap: 10px;">
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #F59E0B, #D97706); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit;">
                    <i class="fas fa-save"></i> به‌روزرسانی برنامه
                </button>
                <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" class="btn" style="background: #718096; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-times"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- Persian Datepicker Assets -->
<!-- ========================================== -->
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<script>
$(document).ready(function() {
    // تابع تبدیل اعداد فارسی به انگلیسی
    function toEnglishDigits(str) {
        if (!str) return str;
        var persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        var englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        for (var i = 0; i < 10; i++) {
            str = str.replace(new RegExp(persianNumbers[i], 'g'), englishNumbers[i]);
        }
        return str;
    }

    // فعال‌سازی برای تمام فیلدهای تاریخ شمسی
    $('.persian-date-input').each(function() {
        var $input = $(this);
        var targetId = $input.data('target');
        var $hiddenInput = $('#' + targetId);
        var $preview = $('#' + targetId.replace('_gregorian', '_preview'));

        $input.persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            },
            toolbox: {
                calendarSwitch: {
                    enabled: false
                }
            },
            onSelect: function(unix) {
                var pd = new persianDate(unix);
                var gDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
                gDate = toEnglishDigits(gDate);
                
                $hiddenInput.val(gDate);
                if ($preview.length) {
                    $preview.text(gDate);
                }
                console.log('تاریخ انتخاب شده (میلادی):', gDate);
            }
        });
    });

    // اعتبارسنجی سمت کلاینت قبل از ارسال فرم
    $('#auditPlanEditForm').on('submit', function(e) {
        var startDate = $('#start_date_gregorian').val();
        var endDate = $('#end_date_gregorian').val();
        
        if (!startDate || !endDate || startDate === '' || endDate === '') {
            e.preventDefault();
            alert('لطفاً تاریخ شروع و پایان را از تقویم انتخاب کنید.');
            return false;
        }
    });
});
</script>