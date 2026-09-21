<?php
/**
 * PdM Analyzer - فرم ویرایش حالت خرابی
 * مسیر: app/software/pdm/views/failure/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش حالت خرابی
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ویرایش: <strong><?= pdm_e($fm['name']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('failure') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('failure', 'update', ['id' => $fm['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات حالت خرابی
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="code">کد</label>
                        <input type="text" id="code" name="code"
                               class="pdm-form-control"
                               value="<?= pdm_e($fm['code'] ?? '') ?>"
                               maxlength="50">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_id">دارایی</label>
                        <select id="asset_id" name="asset_id" class="pdm-form-control" required>
                            <?php foreach ($pdm_assets as $asset): ?>
                                <option value="<?= (int) $asset['id'] ?>"
                                    <?= (int) $fm['asset_id'] === (int) $asset['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($asset['asset_code']) ?> — <?= pdm_e($asset['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="name">نام حالت خرابی</label>
                    <input type="text" id="name" name="name"
                           class="pdm-form-control"
                           value="<?= pdm_e($fm['name']) ?>"
                           required maxlength="150">
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"><?= pdm_e($fm['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator"></i>
                    تحلیل FMEA
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="severity">شدت</label>
                        <input type="number" id="severity" name="severity"
                               class="pdm-form-control"
                               value="<?= (int) ($fm['severity'] ?? 5) ?>"
                               min="1" max="10" required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="occurrence">احتمال تکرار</label>
                        <input type="number" id="occurrence" name="occurrence"
                               class="pdm-form-control"
                               value="<?= (int) ($fm['occurrence'] ?? 5) ?>"
                               min="1" max="10" required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="detection">قابلیت تشخیص</label>
                        <input type="number" id="detection" name="detection"
                               class="pdm-form-control"
                               value="<?= (int) ($fm['detection'] ?? 5) ?>"
                               min="1" max="10" required>
                    </div>
                </div>

                <?php
                $rpn = ((int) $fm['severity']) * ((int) $fm['occurrence']) * ((int) $fm['detection']);
                ?>
                <div class="pdm-alert <?= \App\Software\Pdm\Models\FailureMode::getRiskClass($rpn) ?>">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        RPN فعلی: <strong><?= pdm_num($rpn) ?></strong>
                        — سطح: <strong><?= \App\Software\Pdm\Models\FailureMode::getRiskLabel($rpn) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools"></i>
                    اقدام پیشنهادی
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="recommended_action">اقدام اصلاحی</label>
                    <textarea id="recommended_action" name="recommended_action"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"><?= pdm_e($fm['recommended_action'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= pdm_url('failure') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm.js"></script>