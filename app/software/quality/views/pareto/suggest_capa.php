<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <div style="font-size:0.85rem; color:#94a3b8; margin-bottom:4px;">
                <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $analysis['id'] ?>"
                   style="color:#059669; text-decoration:none;">
                    <i class="fas fa-arrow-right"></i> بازگشت به تحلیل
                </a>
            </div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <i class="fas fa-magic" style="color:#059669;"></i>
                تولید CAPA از Vital Few
            </h1>
            <p style="color:#6b7280; margin-top:4px; font-size:0.875rem;">
                برای هر Vital Few یک اقدام اصلاحی پیشنهاد شده. موارد دلخواه رو تأیید کن.
            </p>
        </div>
    </div>

    <?php if (empty($suggestions)): ?>
        <div class="qc-empty-state">
            <i class="fas fa-info-circle" style="font-size:3rem; color:#cbd5e1;"></i>
            <h3 style="margin-top:1rem; color:#64748b;">پیشنهادی وجود ندارد</h3>
            <p style="color:#94a3b8;">ابتدا تحلیل Pareto رو با داده تکمیل کن.</p>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $analysis['id'] ?>"
               class="qc-btn-primary" style="margin-top:1rem;">
                <i class="fas fa-arrow-right"></i> بازگشت به تحلیل
            </a>
        </div>
    <?php else: ?>
        <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=storeCapaFromPareto">
            <?= $this->csrfField() ?>
            <input type="hidden" name="analysis_id" value="<?= $analysis['id'] ?>">

            <div class="qc-card qc-mb-3">
                <div class="qc-card-header">
                    <h3 class="qc-card-title">
                        <i class="fas fa-fire" style="color:#dc2626;"></i>
                        Vital Few ها (<?= count($suggestions) ?> دسته)
                    </h3>
                </div>
                <div class="qc-card-body">
                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <?php foreach ($suggestions as $i => $sug): ?>
                            <div class="qc-card" style="border:2px solid #e5e7eb; background:#fff;">
                                <div class="qc-card-body">
                                    <!-- چک‌باکس فعال‌سازی -->
                                    <label style="display:flex; align-items:flex-start; gap:12px; cursor:pointer;">
                                        <input type="checkbox" name="selected[]" value="<?= $i ?>" checked
                                               style="margin-top:4px; width:18px; height:18px;"
                                               class="capa-select-checkbox">
                                        <div style="flex:1;">
                                            <div style="font-size:0.75rem; color:#dc2626; margin-bottom:4px;">
                                                <i class="fas fa-fire"></i> Vital Few #<?= $i + 1 ?>
                                            </div>

                                            <div style="margin-bottom:10px;">
                                                <label class="qc-form-label">عنوان اقدام</label>
                                                <input type="text" name="title[<?= $i ?>]"
                                                       class="qc-form-control" required
                                                       value="<?= htmlspecialchars($sug['title']) ?>">
                                            </div>

                                            <div style="background:#fef2f2; padding:8px; border-radius:6px; font-size:0.8rem; color:#7f1d1d; margin-bottom:10px;">
                                                <strong>شرح مشکل:</strong> <?= htmlspecialchars($sug['problem_description']) ?>
                                            </div>

                                            <div class="qc-form-grid-3">
                                                <div>
                                                    <label class="qc-form-label">اولویت</label>
                                                    <select name="priority[<?= $i ?>]" class="qc-form-select">
                                                        <option value="critical" <?= $sug['priority'] === 'critical' ? 'selected' : '' ?>>بحرانی</option>
                                                        <option value="high" <?= $sug['priority'] === 'high' ? 'selected' : '' ?>>بالا</option>
                                                        <option value="medium" <?= $sug['priority'] === 'medium' ? 'selected' : '' ?>>متوسط</option>
                                                        <option value="low" <?= $sug['priority'] === 'low' ? 'selected' : '' ?>>پایین</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="qc-form-label">مسئول</label>
                                                    <input type="text" name="responsible_person[<?= $i ?>]"
                                                           class="qc-form-control" placeholder="نام مسئول">
                                                </div>
                                                <div>
                                                    <label class="qc-form-label">سررسید</label>
                                                    <input type="text" 
                                                        name="due_date_jalali[<?= $i ?>]" 
                                                        id="dueDateJalali_<?= $i ?>"
                                                        class="qc-form-control due-date-jalali" 
                                                        placeholder="۱۴۰۵/۰۷/۱۵"
                                                        autocomplete="off" 
                                                        readonly 
                                                        style="cursor:pointer;">
                                                    <input type="hidden" 
                                                        name="due_date[<?= $i ?>]" 
                                                        id="dueDate_<?= $i ?>">
                                                </div>
                                            </div>

                                            <input type="hidden" name="before_value[<?= $i ?>]"
                                                   value="<?= (float)$sug['before_value'] ?>">
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="qc-flex qc-gap-2" style="justify-content:flex-end;">
                <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $analysis['id'] ?>"
                   class="qc-btn-outline">
                    <i class="fas fa-times"></i> انصراف
                </a>
                <button type="submit" class="qc-btn-primary">
                    <i class="fas fa-check"></i> ثبت CAPA های انتخاب‌شده
                </button>
            </div>
        </form>
    <?php endif; ?>

    <!-- Persian Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
</div>

<script src="/public/assets/js/software/quality.js"></script>
<script>
$(document).ready(function() {
    $('.due-date-jalali').each(function() {
        var $input = $(this);
        var idx = $input.attr('id').replace('dueDateJalali_', '');
        
        $input.persianDatepicker({
            format: 'YYYY/MM/DD',
            initialValue: false,
            autoClose: true,
            persianDigit: true,
            observer: true,
            calendar: {
                persian: {
                    locale: 'fa'
                }
            },
            onSelect: function(unix) {
                // تبدیل به میلادی برای ارسال به سرور
                var pd = new persianDate(unix);
                var gregorian = pd.toCalendar('gregorian');
                var y = gregorian.year();
                var m = String(gregorian.month()).padStart(2, '0');
                var d = String(gregorian.date()).padStart(2, '0');
                $('#dueDate_' + idx).val(y + '-' + m + '-' + d);
            }
        });
    });
});
</script>