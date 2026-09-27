<?php
$isEdit = !empty($capa);
$formAction = $isEdit
    ? CURRENT_MODULE_URL . '?controller=capa&action=update'
    : CURRENT_MODULE_URL . '?controller=capa&action=store';

$sourceLabels = [
    'pareto'             => 'Pareto',
    'control_chart'      => 'نمودار کنترل',
    'capability'         => 'قابلیت فرآیند',
    'msa'                => 'MSA',
    'fmea'               => 'FMEA',
    'audit'              => 'ممیزی',
    'customer_complaint' => 'شکایت مشتری',
    'other'              => 'سایر',
];
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <i class="fas fa-tasks" style="color:#059669;"></i>
                <?= $isEdit ? 'ویرایش CAPA' : 'اقدام اصلاحی جدید' ?>
            </h1>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa" class="qc-btn-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="qc-alert qc-alert-danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= $formAction ?>">
        <?= $this->csrfField() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $capa['id'] ?>">
        <?php endif; ?>

        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> اطلاعات اصلی</h3>
            </div>
            <div class="qc-card-body">
                <div>
                    <label class="qc-form-label">عنوان اقدام <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="title" class="qc-form-control" required
                           value="<?= htmlspecialchars($capa['title'] ?? '') ?>"
                           placeholder="مثلاً: کالیبراسیون دستگاه اندازه‌گیری">
                </div>

                <div class="qc-form-grid-3" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">پروژه (اختیاری)</label>
                        <select name="project_id" class="qc-form-select">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= (int)($capa['project_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="qc-form-label">نوع اقدام</label>
                        <select name="action_type" class="qc-form-select">
                            <option value="corrective" <?= ($capa['action_type'] ?? '') === 'corrective' ? 'selected' : '' ?>>اصلاحی (Corrective)</option>
                            <option value="preventive" <?= ($capa['action_type'] ?? '') === 'preventive' ? 'selected' : '' ?>>پیشگیرانه (Preventive)</option>
                            <option value="improvement" <?= ($capa['action_type'] ?? '') === 'improvement' ? 'selected' : '' ?>>بهبود (Improvement)</option>
                        </select>
                    </div>
                    <div>
                        <label class="qc-form-label">منبع</label>
                        <select name="source_type" class="qc-form-select">
                            <?php foreach ($sourceLabels as $k => $l): ?>
                                <option value="<?= $k ?>" <?= ($capa['source_type'] ?? $sourceType) === $k ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($sourceType && $sourceId): ?>
                    <input type="hidden" name="source_id" value="<?= (int)$sourceId ?>">
                <?php endif; ?>
            </div>
        </div>

        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-search"></i> شرح مشکل و تحلیل ریشه‌ای</h3>
            </div>
            <div class="qc-card-body">
                <div>
                    <label class="qc-form-label">شرح مشکل</label>
                    <textarea name="problem_description" class="qc-form-textarea" rows="3"
                              placeholder="مشکل چی هست؟ کجا و چطور ظاهر شد؟"><?= htmlspecialchars($capa['problem_description'] ?? '') ?></textarea>
                </div>

                <div class="qc-form-grid-2" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">روش تحلیل ریشه‌ای</label>
                        <select name="root_cause_method" class="qc-form-select">
                            <option value="">— انتخاب کنید —</option>
                            <option value="5whys"    <?= ($capa['root_cause_method'] ?? '') === '5whys' ? 'selected' : '' ?>>۵ چرا (5 Whys)</option>
                            <option value="ishikawa" <?= ($capa['root_cause_method'] ?? '') === 'ishikawa' ? 'selected' : '' ?>>استخوان‌ماهی (Ishikawa)</option>
                            <option value="fmea"     <?= ($capa['root_cause_method'] ?? '') === 'fmea' ? 'selected' : '' ?>>FMEA</option>
                            <option value="fta"      <?= ($capa['root_cause_method'] ?? '') === 'fta' ? 'selected' : '' ?>>درخت خطا (FTA)</option>
                            <option value="other"    <?= ($capa['root_cause_method'] ?? '') === 'other' ? 'selected' : '' ?>>سایر</option>
                        </select>
                    </div>
                    <div>
                        <label class="qc-form-label">ریشه‌ی مشکل</label>
                        <input type="text" name="root_cause" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['root_cause'] ?? '') ?>"
                               placeholder="ریشه‌ی اصلی که شناسایی شد">
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <label class="qc-form-label">برنامه‌ی اقدام</label>
                    <textarea name="action_plan" class="qc-form-textarea" rows="3"
                              placeholder="گام‌های اجرایی اقدام..."><?= htmlspecialchars($capa['action_plan'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-user-check"></i> مسئولیت و زمان‌بندی</h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-form-grid-3">
                    <div>
                        <label class="qc-form-label">مسئول اجرا</label>
                        <input type="text" name="responsible_person" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['responsible_person'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="qc-form-label">دپارتمان</label>
                        <input type="text" name="department" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['department'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="qc-form-label">سررسید</label>
                        <input type="text" name="due_date_jalali" id="dueDateJalali"
                            class="qc-form-control" placeholder="۱۴۰۵/۱۲/۲۹"
                            autocomplete="off" readonly style="cursor:pointer;"
                            value="<?= !empty($capa['due_date']) ? qc_date($capa['due_date'], 'Y/m/d') : '' ?>">
                        <input type="hidden" name="due_date" id="dueDate"
                            value="<?= htmlspecialchars($capa['due_date'] ?? '') ?>">
                    </div>
                </div>

                <div class="qc-form-grid-2" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">اولویت</label>
                        <select name="priority" class="qc-form-select">
                            <option value="critical" <?= ($capa['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>بحرانی</option>
                            <option value="high"     <?= ($capa['priority'] ?? '') === 'high' ? 'selected' : '' ?>>بالا</option>
                            <option value="medium"   <?= ($capa['priority'] ?? 'medium') === 'medium' ? 'selected' : '' ?>>متوسط</option>
                            <option value="low"      <?= ($capa['priority'] ?? '') === 'low' ? 'selected' : '' ?>>پایین</option>
                        </select>
                    </div>
                    <div>
                        <label class="qc-form-label">وضعیت</label>
                        <select name="status" class="qc-form-select">
                            <option value="open"        <?= ($capa['status'] ?? 'open') === 'open' ? 'selected' : '' ?>>باز</option>
                            <option value="in_progress" <?= ($capa['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>در حال اجرا</option>
                            <option value="implemented" <?= ($capa['status'] ?? '') === 'implemented' ? 'selected' : '' ?>>اجرا شد</option>
                            <option value="verified"    <?= ($capa['status'] ?? '') === 'verified' ? 'selected' : '' ?>>تأییدشده</option>
                            <option value="closed"      <?= ($capa['status'] ?? '') === 'closed' ? 'selected' : '' ?>>بسته‌شده</option>
                            <option value="cancelled"   <?= ($capa['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>لغو‌شده</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title"><i class="fas fa-chart-line"></i> اثربخشی و هزینه (اختیاری)</h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-form-grid-2">
                    <div>
                        <label class="qc-form-label">مقدار قبل از اقدام</label>
                        <input type="number" step="0.0001" name="before_value" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['before_value'] ?? '') ?>"
                               placeholder="مثلاً: درصد نقص قبل">
                    </div>
                    <div>
                        <label class="qc-form-label">مقدار بعد از اقدام</label>
                        <input type="number" step="0.0001" name="after_value" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['after_value'] ?? '') ?>"
                               placeholder="مثلاً: درصد نقص بعد">
                    </div>
                </div>

                <div class="qc-form-grid-2" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">هزینه برآوردی</label>
                        <input type="number" step="0.0001" name="estimated_cost" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['estimated_cost'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="qc-form-label">هزینه واقعی</label>
                        <input type="number" step="0.0001" name="actual_cost" class="qc-form-control"
                               value="<?= htmlspecialchars($capa['actual_cost'] ?? '') ?>">
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <label class="qc-form-label">یادداشت اثربخشی</label>
                    <textarea name="effectiveness_notes" class="qc-form-textarea" rows="2"><?= htmlspecialchars($capa['effectiveness_notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="qc-flex qc-gap-2" style="justify-content:flex-end;">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=capa" class="qc-btn-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
            <button type="submit" class="qc-btn-primary">
                <i class="fas fa-save"></i>
                <?= $isEdit ? 'ذخیره تغییرات' : 'ثبت CAPA' ?>
            </button>
        </div>
    </form>
</div>

<!-- Persian Datepicker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script src="/public/assets/js/software/quality.js"></script>

<script>
$(document).ready(function() {
    $('#dueDateJalali').persianDatepicker({
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
            var pd = new persianDate(unix);
            var gregorian = pd.toCalendar('gregorian');
            var y = gregorian.year();
            var m = String(gregorian.month()).padStart(2, '0');
            var d = String(gregorian.date()).padStart(2, '0');
            $('#dueDate').val(y + '-' + m + '-' + d);
        }
    });
});
</script>