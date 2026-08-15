<?php
$pageTitle = 'ایجاد جلسه ممیزی جدید';
$currentPage = 'auditsessions';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-plus-circle" style="color: #6C3CE1;"></i>
            ایجاد جلسه ممیزی جدید
        </h1>
        <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به برنامه
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px;">
        <div style="background: #F7FAFC; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-right: 4px solid #6C3CE1;">
            <h3 style="margin: 0 0 10px 0; color: #2D3748;"><?= htmlspecialchars($plan['title']) ?></h3>
            <p style="margin: 0; color: #718096; font-size: 0.9rem;">
                <i class="fas fa-calendar"></i>
                <?= date('Y/m/d', strtotime($plan['start_date'])) ?> تا <?= date('Y/m/d', strtotime($plan['end_date'])) ?>
            </p>
        </div>

        <form method="POST" action="?controller=auditsessions&action=store" id="sessionForm">
            <input type="hidden" name="audit_plan_id" value="<?= $plan['id'] ?>">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    واحد تحت ممیزی <span style="color: #EF4444;">*</span>
                </label>
                <select name="department_id" required
                        style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    <option value="">-- انتخاب واحد --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name_fa']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    تاریخ برنامه‌ریزی شده ممیزی <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" 
                       id="audit_date_display"
                       class="persian-date-input"
                       data-target="audit_date_hidden"
                       placeholder="مثال: ۱۴۰۵/۵/۲۰"
                       required
                       style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                <input type="hidden" name="audit_date" id="audit_date_hidden" value="<?= date('Y-m-d') ?>">
                <small style="color: #718096; font-size: 0.8rem; margin-top: 4px; display: block;">
                    میلادی: <span id="audit_date_preview"><?= date('Y-m-d') ?></span>
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    ممیز مسئول
                </label>
                <select name="assigned_auditor_id"
                        style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    <option value="">-- انتخاب ممیز --</option>
                    <?php foreach ($auditors as $auditor): ?>
                        <option value="<?= $auditor['id'] ?>"><?= htmlspecialchars($auditor['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #E2E8F0; display: flex; gap: 10px;">
                <button type="submit" 
                        style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit;">
                    <i class="fas fa-save"></i> ایجاد جلسه
                </button>
                <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" 
                   style="background: #718096; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-times"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<!-- اسکریپت تاریخ شمسی -->
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>

<script>
$(document).ready(function() {
    function toEnglishDigits(str) {
        if (!str) return str;
        var persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        var english = ['0','1','2','3','4','5','6','7','8','9'];
        for (var i = 0; i < 10; i++) {
            str = str.replace(new RegExp(persian[i], 'g'), english[i]);
        }
        return str;
    }

    $('#audit_date_display').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        calendar: { persian: { locale: 'fa' } },
        toolbox: { calendarSwitch: { enabled: false } },
        onSelect: function(unix) {
            var pd = new persianDate(unix);
            var gDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
            gDate = toEnglishDigits(gDate);
            $('#audit_date_hidden').val(gDate);
            $('#audit_date_preview').text(gDate);
            console.log('تاریخ انتخاب شده:', gDate);
        }
    });

    $('#sessionForm').on('submit', function(e) {
        var auditDate = $('#audit_date_hidden').val();
        if (!auditDate || auditDate === '') {
            e.preventDefault();
            alert('لطفاً تاریخ ممیزی را از تقویم انتخاب کنید.');
            return false;
        }
    });
});
</script>