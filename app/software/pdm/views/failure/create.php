<?php
/**
 * PdM Analyzer - فرم ایجاد حالت خرابی
 * مسیر: app/software/pdm/views/failure/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن حالت خرابی (FMEA)
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                تحلیل حالات خرابی و اثرات آن را ثبت کنید.
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

    <form method="POST" action="<?= pdm_url('failure', 'store') ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
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
                               placeholder="مثال: FM-001"
                               maxlength="50">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_id">
                            دارایی <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <select id="asset_id" name="asset_id" class="pdm-form-control" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_assets as $asset): ?>
                                <option value="<?= (int) $asset['id'] ?>"
                                    <?= $preselectedAssetId === (int) $asset['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($asset['asset_code']) ?> — <?= pdm_e($asset['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="name">
                        نام حالت خرابی <span style="color: var(--pdm-danger);">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                           class="pdm-form-control"
                           placeholder="مثال: نشتی روغن از شفت"
                           required maxlength="150">
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"
                              placeholder="شرح حالت خرابی و چگونگی تشخیص آن..."></textarea>
                </div>
            </div>
        </div>

        <!-- تحلیل FMEA -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calculator"></i>
                    تحلیل FMEA (RPN)
                </h3>
            </div>
            <div class="card-body">
                <p class="pdm-text-muted pdm-mb-3" style="font-size: 0.85rem;">
                    هر پارامتر را از ۱ (کمترین) تا ۱۰ (بیشترین) امتیاز دهید.
                    <strong>RPN = شدت × تکرار × تشخیص</strong>
                </p>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="severity">
                            شدت (Severity)
                        </label>
                        <input type="number" id="severity" name="severity"
                               class="pdm-form-control"
                               value="5" min="1" max="10" required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="occurrence">
                            احتمال تکرار (Occurrence)
                        </label>
                        <input type="number" id="occurrence" name="occurrence"
                               class="pdm-form-control"
                               value="5" min="1" max="10" required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="detection">
                            قابلیت تشخیص (Detection)
                        </label>
                        <input type="number" id="detection" name="detection"
                               class="pdm-form-control"
                               value="5" min="1" max="10" required>
                    </div>
                </div>

                <div class="pdm-alert info pdm-mt-3">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        <strong>راهنما:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-right: 1.25rem; font-size: 0.85rem;">
                            <li><strong>شدت:</strong> ۱ = بی‌اهمیت، ۱۰ = فاجعه‌بار</li>
                            <li><strong>تکرار:</strong> ۱ = نادر، ۱۰ = دائمی</li>
                            <li><strong>تشخیص:</strong> ۱ = قطعی، ۱۰ = غیرقابل تشخیص</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- اقدام پیشنهادی -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools"></i>
                    اقدام پیشنهادی
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="recommended_action">
                        اقدام اصلاحی پیشنهادی
                    </label>
                    <textarea id="recommended_action" name="recommended_action"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"
                              placeholder="اقدامات لازم برای کاهش RPN..."></textarea>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= pdm_url('failure') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm.js"></script>