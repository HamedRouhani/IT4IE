<?php
/**
 * ویو ویرایش جلسه ممیزی - ماژول QMS
 * مسیر: app/software/qms/views/audit-sessions/edit.php
 */
$pageTitle = 'ویرایش جلسه ممیزی';
$currentPage = 'auditsessions';

// تبدیل تاریخ میلادی به شمسی برای نمایش
$auditDateShamsi = function_exists('jdate') ? jdate('Y/m/d', strtotime($session['audit_date'])) : $session['audit_date'];
$actualDateShamsi = !empty($session['actual_date']) ? (function_exists('jdate') ? jdate('Y/m/d', strtotime($session['actual_date'])) : $session['actual_date']) : '';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-edit" style="color: #F59E0B;"></i>
            ویرایش جلسه ممیزی
        </h1>
        <a href="?controller=auditsessions&action=show&id=<?= $session['id'] ?>" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به جلسه
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px;">
        <!-- اطلاعات برنامه -->
        <div style="background: #F7FAFC; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-right: 4px solid #6C3CE1;">
            <h3 style="margin: 0 0 5px 0; color: #2D3748; font-size: 1.1rem;">
                <?= htmlspecialchars($session['plan_title'] ?? '') ?>
            </h3>
            <p style="margin: 0; color: #718096; font-size: 0.9rem;">
                <i class="fas fa-building"></i> واحد: <?= htmlspecialchars($session['department_name'] ?? '-') ?>
            </p>
        </div>

        <form method="POST" action="?controller=auditsessions&action=update&id=<?= $session['id'] ?>" id="editSessionForm">
            
            <!-- تاریخ برنامه‌ریزی شده -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    تاریخ برنامه‌ریزی شده ممیزی <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" 
                       id="audit_date_display"
                       class="persian-date-input"
                       data-target="audit_date_hidden"
                       placeholder="مثال: ۱۰۵/۰۵/۲۰"
                       value="<?= $auditDateShamsi ?>"
                       required
                       style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                <input type="hidden" name="audit_date" id="audit_date_hidden" value="<?= htmlspecialchars($session['audit_date']) ?>">
                <small style="color: #718096; font-size: 0.8rem; margin-top: 4px; display: block;">
                    میلادی: <span id="audit_date_preview"><?= htmlspecialchars($session['audit_date']) ?></span>
                </small>
            </div>

            <!-- تاریخ واقعی انجام -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    تاریخ واقعی انجام (اختیاری)
                </label>
                <input type="text" 
                       id="actual_date_display"
                       class="persian-date-input"
                       data-target="actual_date_hidden"
                       placeholder="مثال: ۱۴۰۵/۰۵/۲۰"
                       value="<?= $actualDateShamsi ?>"
                       style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                <input type="hidden" name="actual_date" id="actual_date_hidden" value="<?= htmlspecialchars($session['actual_date'] ?? '') ?>">
                <small style="color: #718096; font-size: 0.8rem; margin-top: 4px; display: block;">
                    میلادی: <span id="actual_date_preview"><?= htmlspecialchars($session['actual_date'] ?? '-') ?></span>
                </small>
            </div>

            <!-- ممیز مسئول -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    ممیز مسئول
                </label>
                <select name="assigned_auditor_id"
                        style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    <option value="">-- انتخاب ممیز --</option>
                    <?php foreach ($auditors as $auditor): ?>
                        <option value="<?= $auditor['id'] ?>" 
                                <?= $session['assigned_auditor_id'] == $auditor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($auditor['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- وضعیت جلسه -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    وضعیت جلسه
                </label>
                <select name="overall_status"
                        style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;">
                    <option value="not_started" <?= $session['overall_status'] === 'not_started' ? 'selected' : '' ?>>شروع نشده</option>
                    <option value="in_progress" <?= $session['overall_status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                    <option value="completed" <?= $session['overall_status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                    <option value="postponed" <?= $session['overall_status'] === 'postponed' ? 'selected' : '' ?>>به تعویق افتاده</option>
                </select>
            </div>

            <!-- یادداشت ممیز -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">
                    یادداشت ممیز
                </label>
                <textarea name="auditor_notes" rows="4"
                          style="width: 100%; padding: 10px 15px; border: 2px solid #E2E8F0; border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($session['auditor_notes'] ?? '') ?></textarea>
            </div>

            <!-- دکمه‌های عملیات -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #E2E8F0; display: flex; gap: 10px;">
                <button type="submit" 
                        style="background: linear-gradient(135deg, #F59E0B, #D97706); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-family: inherit;">
                    <i class="fas fa-save"></i> به‌روزرسانی جلسه
                </button>
                <a href="?controller=auditsessions&action=show&id=<?= $session['id'] ?>" 
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
        var persian = ['','۱','۲','۳','','۵','۶','۷','','۹'];
        var english = ['0','1','2','3','4','5','6','7','8','9'];
        for (var i = 0; i < 10; i++) {
            str = str.replace(new RegExp(persian[i], 'g'), english[i]);
        }
        return str;
    }

    // فعال‌سازی تقویم برای تاریخ برنامه‌ریزی
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
        }
    });

    // فعال‌سازی تقویم برای تاریخ واقعی
    $('#actual_date_display').persianDatepicker({
        format: 'YYYY/MM/DD',
        autoClose: true,
        initialValue: false,
        calendar: { persian: { locale: 'fa' } },
        toolbox: { calendarSwitch: { enabled: false } },
        onSelect: function(unix) {
            var pd = new persianDate(unix);
            var gDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
            gDate = toEnglishDigits(gDate);
            $('#actual_date_hidden').val(gDate);
            $('#actual_date_preview').text(gDate);
        }
    });

    // اعتبارسنجی فرم
    $('#editSessionForm').on('submit', function(e) {
        var auditDate = $('#audit_date_hidden').val();
        if (!auditDate || auditDate === '') {
            e.preventDefault();
            alert('لطفاً تاریخ برنامه‌ریزی شده را از تقویم انتخاب کنید.');
            return false;
        }
    });
});
</script>