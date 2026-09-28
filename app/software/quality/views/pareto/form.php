<?php
$isEdit = !empty($analysis);
$pageTitle = $isEdit ? 'ویرایش تحلیل Pareto' : 'تحلیل Pareto جدید';
$formAction = $isEdit
    ? CURRENT_MODULE_URL . '?controller=pareto&action=update'
    : CURRENT_MODULE_URL . '?controller=pareto&action=store';
$initialItems = [];
if (!empty($items)) {
    foreach ($items as $it) {
        $initialItems[] = [
            'category_name' => $it['category_name'],
            'value'         => (float)$it['value'],
            'frequency'     => $it['frequency'],
            'cost_per_unit' => $it['cost_per_unit'],
        ];
    }
}
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <i class="fas fa-chart-bar" style="color:#059669;"></i>
                <?= $isEdit ? 'ویرایش تحلیل' : 'تحلیل Pareto جدید' ?>
            </h1>
            <p style="color:#6b7280; margin-top:4px; font-size:0.875rem;">
                دسته‌بندی‌ها و مقادیر را وارد کن — محاسبات Pareto خودکار انجام می‌شود
            </p>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto" class="qc-btn-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="qc-alert qc-alert-danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= $formAction ?>" id="paretoForm">
        <?= $this->csrfField() ?>
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $analysis['id'] ?>">
        <?php endif; ?>

        <!-- بخش ۱: اطلاعات کلی -->
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title">
                    <i class="fas fa-info-circle"></i> اطلاعات کلی
                </h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-form-grid-2">
                    <div>
                        <label class="qc-form-label">عنوان تحلیل <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="title" class="qc-form-control" required
                               value="<?= htmlspecialchars($analysis['title'] ?? '') ?>"
                               placeholder="مثلاً: تحلیل نقص‌های خط تولید شماره ۲">
                    </div>
                    <div>
                        <label class="qc-form-label">پروژه (اختیاری)</label>
                        <select name="project_id" class="qc-form-select">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= (int)($analysis['project_id'] ?? 0) === (int)$p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top:1rem;">
                    <label class="qc-form-label">شرح مسئله</label>
                    <textarea name="problem_statement" class="qc-form-textarea" rows="3"
                              placeholder="مشکل اصلی چی هست؟ چرا این تحلیل رو انجام می‌دی؟"><?= htmlspecialchars($analysis['problem_statement'] ?? '') ?></textarea>
                </div>

                <div class="qc-form-grid-3" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">نوع دسته‌بندی</label>
                        <input type="text" name="category_type" class="qc-form-control"
                               value="<?= htmlspecialchars($analysis['category_type'] ?? '') ?>"
                               placeholder="مثلاً: نوع نقص">
                    </div>
                    <div>
                        <label class="qc-form-label">واحد</label>
                        <input type="text" name="unit" class="qc-form-control"
                               value="<?= htmlspecialchars($analysis['unit'] ?? '') ?>"
                               placeholder="مثلاً: تعداد، ریال، دقیقه">
                    </div>
                    <div>
                        <label class="qc-form-label">تاریخ تحلیل</label>
                        <input type="text" name="analysis_date_jalali" id="analysisDateJalali"
                            class="qc-form-control" placeholder="۱۴۰۵/۰۷/۰۴" 
                            autocomplete="off" readonly style="cursor:pointer;"
                            value="<?= !empty($analysis['analysis_date']) ? qc_date($analysis['analysis_date'], 'Y/m/d') : qc_today('Y/m/d') ?>">
                        <input type="hidden" name="analysis_date" id="analysisDate"
                            value="<?= htmlspecialchars($analysis['analysis_date'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <div class="qc-form-grid-2" style="margin-top:1rem;">
                    <div>
                        <label class="qc-form-label">بازه از</label>
                        <input type="text" name="period_from_jalali" id="periodFromJalali"
                            class="qc-form-control" placeholder="۱۴۰۵/۰۱/۰۱"
                            autocomplete="off" readonly style="cursor:pointer;"
                            value="<?= !empty($analysis['period_from']) ? qc_date($analysis['period_from'], 'Y/m/d') : '' ?>">
                        <input type="hidden" name="period_from" id="periodFrom"
                            value="<?= htmlspecialchars($analysis['period_from'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="qc-form-label">بازه تا</label>
                        <input type="text" name="period_to_jalali" id="periodToJalali"
                            class="qc-form-control" placeholder="۱۴۰۵/۱۲/۲۹"
                            autocomplete="off" readonly style="cursor:pointer;"
                            value="<?= !empty($analysis['period_to']) ? qc_date($analysis['period_to'], 'Y/m/d') : '' ?>">
                        <input type="hidden" name="period_to" id="periodTo"
                            value="<?= htmlspecialchars($analysis['period_to'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش ۲: آیتم‌های Pareto -->
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header qc-flex qc-flex-between">
                <h3 class="qc-card-title">
                    <i class="fas fa-list"></i> دسته‌بندی‌ها
                </h3>
                <div class="qc-flex qc-gap-2" style="align-items:center;">
                    <label class="qc-form-label" style="margin:0; font-size:0.8rem;">آستانه Vital Few:</label>
                    <input type="number" 
                        name="threshold" 
                        id="thresholdInput"
                        class="qc-form-control" 
                        style="width:80px;"
                        value="<?= htmlspecialchars($analysis['threshold'] ?? 80) ?>"
                        min="1" max="100" step="1">
                    <span style="font-size:0.8rem; color:#64748b;">٪</span>
                    <button type="button" class="qc-btn-primary" id="addItemBtn" style="padding:6px 12px; font-size:0.85rem;">
                        <i class="fas fa-plus"></i> افزودن دسته
                    </button>
                </div>
            </div>
            <div class="qc-card-body">
                <div class="qc-table-wrapper" style="overflow-x:auto;">
                    <table class="qc-table" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>نام دسته <span style="color:#dc2626;">*</span></th>
                                <th style="width:130px;">مقدار</th>
                                <th style="width:120px;">تعداد (اختیاری)</th>
                                <th style="width:130px;">هزینه واحد</th>
                                <th style="width:60px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- JS این رو پر می‌کنه -->
                        </tbody>
                    </table>
                </div>
                <p style="color:#94a3b8; font-size:0.8rem; margin-top:0.5rem;">
                    <i class="fas fa-info-circle"></i>
                    اگه «تعداد» و «هزینه واحد» رو پر کنی، مقدار به‌طور خودکار محاسبه می‌شود.
                </p>
            </div>
        </div>

        <!-- بخش ۳: پیش‌نمایش زنده -->
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title">
                    <i class="fas fa-chart-pie"></i> پیش‌نمایش زنده
                </h3>
            </div>
            <div class="qc-card-body">
                <div id="previewArea">
                    <div class="qc-empty-state" style="padding:2rem;">
                        <i class="fas fa-chart-pie" style="font-size:2rem; color:#cbd5e1;"></i>
                        <p style="color:#94a3b8;">داده‌ای برای پیش‌نمایش وارد نشده</p>
                    </div>
                </div>
                <div id="previewSummary" style="display:none; margin-top:1rem; padding:1rem; background:#f0fdf4; border-radius:8px; border-right:4px solid #059669;">
                    <div class="qc-flex qc-gap-3" style="flex-wrap:wrap;">
                        <div><strong>مجموع:</strong> <span id="pvTotal">0</span></div>
                        <div><strong>Vital Few:</strong> <span id="pvVital">0</span> دسته</div>
                        <div><strong>سهم Vital Few:</strong> <span id="pvVitalPct">0</span>٪</div>
                        <div><strong>Trivial Many:</strong> <span id="pvTrivial">0</span> دسته</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش ۴: وضعیت و یادداشت -->
        <div class="qc-card qc-mb-3">
            <div class="qc-card-header">
                <h3 class="qc-card-title">
                    <i class="fas fa-cog"></i> وضعیت
                </h3>
            </div>
            <div class="qc-card-body">
                <div class="qc-form-grid-2">
                    <div>
                        <label class="qc-form-label">وضعیت</label>
                        <select name="status" class="qc-form-select">
                            <option value="draft" <?= ($analysis['status'] ?? '') === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
                            <option value="analyzed" <?= ($analysis['status'] ?? '') === 'analyzed' ? 'selected' : '' ?>>تحلیل‌شده</option>
                            <option value="action_taken" <?= ($analysis['status'] ?? '') === 'action_taken' ? 'selected' : '' ?>>اقدام انجام شد</option>
                            <option value="closed" <?= ($analysis['status'] ?? '') === 'closed' ? 'selected' : '' ?>>بسته‌شده</option>
                        </select>
                    </div>
                    <div style="display:flex; align-items:flex-end;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="generate_capa" value="1">
                            <span>پس از ثبت، CAPA برای Vital Few ها پیشنهاد بده</span>
                        </label>
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <label class="qc-form-label">یادداشت</label>
                    <textarea name="notes" class="qc-form-textarea" rows="2"
                              placeholder="یادداشت‌های اضافی..."><?= htmlspecialchars($analysis['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- دکمه‌ها -->
        <div class="qc-flex qc-gap-2" style="justify-content:flex-end;">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto" class="qc-btn-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
            <button type="submit" class="qc-btn-primary">
                <i class="fas fa-save"></i>
                <?= $isEdit ? 'ذخیره تغییرات' : 'ثبت تحلیل' ?>
            </button>
        </div>
    </form>
    <!-- Persian Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-date@1.1.0/dist/persian-date.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/assets/js/software/quality.js"></script>

<script>
// داده‌ی اولیه
window.PARETO_INITIAL_ITEMS = <?= json_encode($initialItems, JSON_UNESCAPED_UNICODE) ?>;
window.PARETO_PREVIEW_URL   = '<?= CURRENT_MODULE_URL ?>?controller=pareto&action=preview';
window.QC_CSRF_TOKEN        = '<?= $this->csrfToken() ?>';
</script>

<script src="/assets/js/software/pareto.js?v=<?= time() ?>"></script>
