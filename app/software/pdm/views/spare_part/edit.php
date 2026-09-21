<?php
/**
 * PdM Analyzer - فرم ویرایش قطعه یدکی
 * مسیر: app/software/pdm/views/spare_part/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش قطعه یدکی
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ویرایش: <strong><?= pdm_e($part['name']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('spare_part') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('spare_part', 'update', ['id' => $part['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات قطعه
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="code">کد قطعه</label>
                        <input type="text" id="code" name="code"
                               class="pdm-form-control"
                               value="<?= pdm_e($part['code']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="name">نام قطعه</label>
                        <input type="text" id="name" name="name"
                               class="pdm-form-control"
                               value="<?= pdm_e($part['name']) ?>"
                               required maxlength="150">
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="manufacturer">سازنده</label>
                        <input type="text" id="manufacturer" name="manufacturer"
                               class="pdm-form-control"
                               value="<?= pdm_e($part['manufacturer'] ?? '') ?>"
                               maxlength="100">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit"
                               class="pdm-form-control"
                               value="<?= pdm_e($part['unit'] ?? 'عدد') ?>"
                               maxlength="30">
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="stock_quantity">موجودی فعلی</label>
                        <input type="number" id="stock_quantity" name="stock_quantity"
                               class="pdm-form-control"
                               value="<?= (int) $part['stock_quantity'] ?>"
                               min="0">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="minimum_stock">حد مجاز</label>
                        <input type="number" id="minimum_stock" name="minimum_stock"
                               class="pdm-form-control"
                               value="<?= (int) $part['minimum_stock'] ?>"
                               min="0">
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"><?= pdm_e($part['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= pdm_url('spare_part') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm.js"></script>