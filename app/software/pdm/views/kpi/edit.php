<?php
/**
 * PdM Analyzer - فرم ویرایش شاخص
 * مسیر: app/software/pdm/views/kpi/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش شاخص
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ویرایش: <strong><?= pdm_e($kpi['name']) ?></strong>
                <?php if ((int) $kpi['is_builtin'] === 1): ?>
                    <span class="pdm-criticality-badge pdm-criticality-medium"
                          style="margin-right: 0.5rem;">داخلی</span>
                <?php endif; ?>
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

    <?php if ((int) $kpi['is_builtin'] === 1): ?>
        <div class="pdm-alert warning pdm-mb-3">
            <i class="fas fa-lock"></i>
            <div>
                این شاخص <strong>داخلی</strong> است. فقط می‌توانید نام، واحد، رنگ و آیکون آن را تغییر دهید.
                کد و فرمول قابل ویرایش نیستند.
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('kpi', 'update', ['id' => $kpi['id']]) ?>">
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
                            کد شاخص
                            <?php if ((int) $kpi['is_builtin'] !== 1): ?>
                                <span style="color: var(--pdm-danger);">*</span>
                            <?php endif; ?>
                        </label>
                        <input type="text" id="code" name="code"
                               class="pdm-form-control"
                               value="<?= pdm_e($kpi['code']) ?>"
                               <?= (int) $kpi['is_builtin'] === 1 ? 'readonly' : '' ?>
                               pattern="[A-Z0-9_]+"
                               maxlength="50"
                               style="text-transform: uppercase; <?= (int) $kpi['is_builtin'] === 1 ? 'background: #f3f4f6;' : '' ?>">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="name">
                            نام شاخص <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="pdm-form-control"
                               value="<?= pdm_e($kpi['name']) ?>"
                               required maxlength="150">
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="1000"><?= pdm_e($kpi['description'] ?? '') ?></textarea>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="formula">فرمول</label>
                        <input type="text" id="formula" name="formula"
                               class="pdm-form-control"
                               value="<?= pdm_e($kpi['formula'] ?? '') ?>"
                               <?= (int) $kpi['is_builtin'] === 1 ? 'readonly' : '' ?>
                               maxlength="255"
                               style="<?= (int) $kpi['is_builtin'] === 1 ? 'background: #f3f4f6;' : '' ?>">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit"
                               class="pdm-form-control"
                               value="<?= pdm_e($kpi['unit'] ?? '') ?>"
                               maxlength="50">
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="color">رنگ</label>
                        <input type="color" id="color" name="color"
                               class="pdm-form-control"
                               value="<?= pdm_e($kpi['color'] ?? '#0F766E') ?>"
                               style="height: 45px; padding: 0.25rem;">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="icon">آیکون</label>
                        <select id="icon" name="icon" class="pdm-form-control">
                            <?php
                            $icons = [
                                'fas fa-chart-line'         => '📈 chart-line',
                                'fas fa-clock'              => '🕐 clock',
                                'fas fa-history'            => '📜 history',
                                'fas fa-percentage'         => '٪ percentage',
                                'fas fa-cogs'               => '⚙️ cogs',
                                'fas fa-exclamation-circle' => '⚠️ exclamation',
                                'fas fa-shield-alt'         => '🛡️ shield',
                                'fas fa-money-bill'         => '💰 money',
                                'fas fa-tachometer-alt'     => '⏱️ tachometer',
                                'fas fa-check-circle'       => '✅ check',
                            ];
                            $currentIcon = $kpi['icon'] ?? 'fas fa-chart-line';
                            foreach ($icons as $val => $label):
                            ?>
                                <option value="<?= pdm_e($val) ?>"
                                    <?= $currentIcon === $val ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="sort_order">ترتیب نمایش</label>
                        <input type="number" id="sort_order" name="sort_order"
                               class="pdm-form-control"
                               value="<?= (int) ($kpi['sort_order'] ?? 0) ?>"
                               min="0" max="999">
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label">
                        <input type="checkbox" name="is_active" value="1"
                            <?= (int) $kpi['is_active'] === 1 ? 'checked' : '' ?>>
                        فعال باشد (در کارت‌های داشبورد نمایش داده شود)
                    </label>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= pdm_url('kpi') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm.js"></script>