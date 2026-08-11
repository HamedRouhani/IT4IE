<?php
/**
 * ویو ایجاد برنامه ممیزی جدید - ماژول QMS
 * مسیر: app/software/qms/views/audit-plans/create.php
 */

// دریافت داده‌ها از session (پس از redirect)
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['errors'], $_SESSION['old_input']);

// مقادیر پیش‌فرض
$oldTitle = $old['title'] ?? '';
$oldAuditType = $old['audit_type'] ?? 'internal';
$oldStartDate = $old['start_date'] ?? date('Y-m-d');
$oldEndDate = $old['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
$oldLeadAuditorId = $old['lead_auditor_id'] ?? '';
$oldScope = $old['scope'] ?? '';
$oldObjectives = $old['objectives'] ?? '';
$oldCriteria = $old['criteria'] ?? 'ISO 9001:2015';
$oldDepartments = $old['departments'] ?? [];

// تبدیل تاریخ میلادی به شمسی برای نمایش در فیلد
function miladiToShamsi($miladiDate) {
    if (empty($miladiDate)) return '';
    if (function_exists('jdate')) {
        return jdate('Y/m/d', strtotime($miladiDate));
    }
    return $miladiDate;
}

$startDateShamsi = miladiToShamsi($oldStartDate);
$endDateShamsi = miladiToShamsi($oldEndDate);
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-plus-circle" style="color: #6C3CE1;"></i>
            ایجاد برنامه ممیزی جدید
        </h1>
        <a href="?controller=auditplans" class="btn" style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <strong><i class="fas fa-exclamation-circle"></i> خطاهای زیر رخ داد:</strong>
            <ul class="mb-0" style="margin-top: 10px; padding-right: 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="card" style="background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px;">
        <form method="POST" action="?controller=auditplans&action=store">
            
            <!-- ردیف ۱: عنوان و نوع ممیزی -->
            <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div class="col-md-8" style="flex: 2;">
                    <div class="form-group">
                        <label for="title" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            عنوان برنامه ممیزی <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="title" 
                               name="title" 
                               value="<?= htmlspecialchars($oldTitle) ?>" 
                               placeholder="مثال: ممیزی داخلی سالانه ۱۴۰۵"
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    </div>
                </div>
                <div class="col-md-4" style="flex: 1;">
                    <div class="form-group">
                        <label for="audit_type" style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            نوع ممیزی
                        </label>
                        <select class="form-control" 
                                id="audit_type" 
                                name="audit_type"
                                style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                            <option value="internal" <?= $oldAuditType === 'internal' ? 'selected' : '' ?>>داخلی</option>
                            <option value="external" <?= $oldAuditType === 'external' ? 'selected' : '' ?>>خارجی</option>
                            <option value="surveillance" <?= $oldAuditType === 'surveillance' ? 'selected' : '' ?>>نظارتی</option>
                            <option value="recertification" <?= $oldAuditType === 'recertification' ? 'selected' : '' ?>>تمدید گواهینامه</option>
                            <option value="special" <?= $oldAuditType === 'special' ? 'selected' : '' ?>>ویژه</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ردیف : تاریخ شروع و پایان (شمسی) -->
            <div class="row" style="display: flex; gap: 20px; margin-bottom: 20px;">
                <div class="col-md-6" style="flex: 1;">
                    <div class="form-group">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            تاریخ شروع <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control persian-date-input" 
                               data-target="start_date_gregorian" 
                               placeholder="مثال: ۱۴۰/۰۵/۲۰" 
                               autocomplete="off" 
                               value="<?= $startDateShamsi ?>"
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                        
                        <input type="hidden" 
                               name="start_date" 
                               id="start_date_gregorian" 
                               value="<?= htmlspecialchars($oldStartDate) ?>">
                    </div>
                </div>
                <div class="col-md-6" style="flex: 1;">
                    <div class="form-group">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                            تاریخ پایان <span style="color: #EF4444;">*</span>
                        </label>
                        <input type="text" 
                               class="form-control persian-date-input" 
                               data-target="end_date_gregorian" 
                               placeholder="مثال: ۱۴۰/۰۵/۲۵" 
                               autocomplete="off" 
                               value="<?= $endDateShamsi ?>"
                               required
                               style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                        
                        <input type="hidden" 
                               name="end_date" 
                               id="end_date_gregorian" 
                               value="<?= htmlspecialchars($oldEndDate) ?>">
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
                                    <?= $oldLeadAuditorId == $auditor['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($auditor['full_name']) ?>
                                <?= !empty($auditor['lead_auditor']) ? '(سرممیز)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>هیچ ممیزی ثبت نشده است</option>
                    <?php endif; ?>
                </select>
                <?php if (empty($auditors)): ?>
                    <small style="color: #EF4444; display: block; margin-top: 5px;">
                        ⚠️ ابتدا باید از بخش «ممیزان» یک ممیز ثبت کنید.
                    </small>
                <?php endif; ?>
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
                          placeholder="مثال: کلیه فرآیندهای تولید و کنترل کیفیت کارخانه اصلی"
                          style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($oldScope) ?></textarea>
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
                          placeholder="مثال: ارزیابی انطباق با ISO 9001:2015 و شناسایی فرصت‌های بهبود"
                          style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($oldObjectives) ?></textarea>
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
                       value="<?= htmlspecialchars($oldCriteria) ?>"
                       placeholder="ISO 9001:2015"
                       style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
            </div>

            <!-- واحدهای تحت ممیزی -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 12px; color: #4A5568;">
                    واحدهای تحت ممیزی
                </label>
                <div class="row" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php if (!empty($departments)): ?>
                        <?php foreach ($departments as $dept): ?>
                            <div class="col-md-4" style="flex: 1; min-width: 200px;">
                                <div style="display: flex; align-items: center; gap: 8px; padding: 10px; background: #F7FAFC; border-radius: 8px; border: 1px solid #E2E8F0;">
                                    <input type="checkbox" 
                                           id="dept_<?= $dept['id'] ?>" 
                                           name="departments[]" 
                                           value="<?= $dept['id'] ?>"
                                           <?= in_array($dept['id'], $oldDepartments) ? 'checked' : '' ?>
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
                            هیچ واحد سازمانی ثبت نشده است. ابتدا از بخش «واحدها» واحد اضافه کنید.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- دکمه‌های عملیات -->
            <div class="form-group" style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #E2E8F0; display: flex; gap: 10px;">
                <button type="submit" class="btn" style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit;">
                    <i class="fas fa-save"></i> ذخیره برنامه
                </button>
                <a href="?controller=auditplans" class="btn" style="background: #718096; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
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
    // تنظیمات مشترک تقویم
    var datepickerOptions = {
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
        }
    };

    // فعال‌سازی برای تمام فیلدهای تاریخ شمسی
    $('.persian-date-input').each(function() {
        var $input = $(this);
        var targetId = $input.data('target');
        var $hiddenInput = $('#' + targetId);

        $input.persianDatepicker({
            ...datepickerOptions,
            onSelect: function(unix) {
                // تبدیل تاریخ انتخاب شده به میلادی و قرار دادن در فیلد مخفی
                var pd = new persianDate(unix);
                var gDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
                $hiddenInput.val(gDate);
                
                // نمایش تاریخ انتخاب شده در کنسول برای دیباگ
                console.log('تاریخ شمسی انتخاب شده:', pd.format('YYYY/MM/DD'));
                console.log('تاریخ میلادی معادل:', gDate);
            }
        });
    });

    // اعتبارسنجی سمت کلاینت: تاریخ پایان نباید قبل از تاریخ شروع باشد
    $('form').on('submit', function(e) {
        var startDate = $('#start_date_gregorian').val();
        var endDate = $('#end_date_gregorian').val();
        
        if (startDate && endDate && endDate < startDate) {
            e.preventDefault();
            alert('تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد!');
            return false;
        }
    });
});
</script>