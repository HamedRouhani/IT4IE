<?php
/**
 * PdM Analyzer - فرم ایجاد شاخص جدید
 * مسیر: app/software/pdm/views/kpi/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن شاخص جدید
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                یک شاخص سفارشی برای سیستم خود تعریف کنید.
            </p>
        </div>
        <a href="<?= pdm_url('kpi') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('kpi', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات شاخص
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="code">
                            کد شاخص <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text" id="code" name="code"
                               class="pdm-form-control"
                               placeholder="مثال: CUSTOM_KPI_1"
                               pattern="[A-Z0-9_]+"
                               title="فقط حروف انگلیسی بزرگ، اعداد و _"
                               required maxlength="50"
                               style="text-transform: uppercase;">
                        <small class="pdm-text-muted">
                            فقط حروف انگلیسی بزرگ، اعداد و _ (زیرخط).
                        </small>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="name">
                            نام شاخص <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="pdm-form-control"
                               placeholder="مثال: شاخص کیفیت تعمیرات"
                               required maxlength="150">
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="1000"
                              placeholder="توضیح کاربرد این شاخص..."></textarea>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="formula">فرمول (اختیاری)</label>
                        <input type="text" id="formula" name="formula"
                               class="pdm-form-control"
                               placeholder="مثال: Total Cost / Number of Repairs"
                               maxlength="255">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit"
                               class="pdm-form-control"
                               placeholder="مثال: ساعت، درصد، عدد..."
                               maxlength="50">
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="color">رنگ</label>
                        <input type="color" id="color" name="color"
                               class="pdm-form-control"
                               value="#0F766E"
                               style="height: 45px; padding: 0.25rem;">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="icon">آیکون (FontAwesome)</label>
                        <select id="icon" name="icon" class="pdm-form-control">
                            <option value="fas fa-chart-line">📈 chart-line</option>
                            <option value="fas fa-clock">🕐 clock</option>
                            <option value="fas fa-history">📜 history</option>
                            <option value="fas fa-percentage">٪ percentage</option>
                            <option value="fas fa-cogs">⚙️ cogs</option>
                            <option value="fas fa-exclamation-circle">⚠️ exclamation</option>
                            <option value="fas fa-shield-alt">🛡️ shield</option>
                            <option value="fas fa-money-bill">💰 money</option>
                            <option value="fas fa-tachometer-alt">⏱️ tachometer</option>
                            <option value="fas fa-check-circle">✅ check</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="sort_order">ترتیب نمایش</label>
                        <input type="number" id="sort_order" name="sort_order"
                               class="pdm-form-control"
                               value="10" min="0" max="999">
                        <small class="pdm-text-muted">عدد کوچک‌تر = نمایش بالاتر.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="pdm-alert info pdm-mb-3">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>نکته:</strong>
                KPIهای سفارشی در حال حاضر به صورت «مقدار دستی» کار می‌کنند و
                محاسبه خودکار فقط برای شاخص‌های داخلی (MTTR, MTBF, ...) فعال است.
                در نسخه‌های بعدی، محاسبه خودکار KPIهای سفارشی از روی فرمول اضافه می‌شود.
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره شاخص
            </button>
            <a href="<?= pdm_url('kpi') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm.js"></script>