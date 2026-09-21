<?php
/**
 * PdM Analyzer - فرم ویرایش دستورکار
 * مسیر: app/software/pdm/views/workorder/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش دستورکار
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ویرایش: <code><?= pdm_e($wo['wo_number']) ?></code>
            </p>
        </div>
        <a href="<?= pdm_url('workorder', 'show', ['id' => $wo['id']]) ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('workorder', 'update', ['id' => $wo['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات دستورکار
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="title">
                        عنوان <span style="color: var(--pdm-danger);">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           class="pdm-form-control"
                           value="<?= pdm_e($wo['title']) ?>"
                           required maxlength="150">
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_id">دارایی</label>
                        <select id="asset_id" name="asset_id" class="pdm-form-control" required>
                            <?php foreach ($pdm_assets as $asset): ?>
                                <option value="<?= (int) $asset['id'] ?>"
                                    <?= (int) $wo['asset_id'] === (int) $asset['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($asset['asset_code']) ?> — <?= pdm_e($asset['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="maintenance_type_id">نوع نگهداری</label>
                        <select id="maintenance_type_id" name="maintenance_type_id" class="pdm-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_types as $type): ?>
                                <option value="<?= (int) $type['id'] ?>"
                                    <?= (int) $wo['maintenance_type_id'] === (int) $type['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="4"
                              maxlength="3000"><?= pdm_e($wo['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flag"></i>
                    وضعیت و زمان‌بندی
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="pdm-form-control">
                            <?php foreach ($priorityOptions as $key => $label): ?>
                                <option value="<?= pdm_e($key) ?>"
                                    <?= $wo['priority'] === $key ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="pdm-form-control">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= pdm_e($key) ?>"
                                    <?= $wo['status'] === $key ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="planned_date">تاریخ برنامه (شمسی)</label>
                        <input type="text" id="planned_date" name="planned_date"
                               class="pdm-form-control pdm-datepicker"
                               value="<?= $wo['planned_date'] ? pdm_e(\App\Helpers\DateHelper::toJalali($wo['planned_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10"
                               autocomplete="off">
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="resolution">اقدام انجام‌شده</label>
                    <textarea id="resolution" name="resolution"
                              class="pdm-form-control" rows="3"
                              maxlength="3000"><?= pdm_e($wo['resolution'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= pdm_url('workorder', 'show', ['id' => $wo['id']]) ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/pdm.js?v=<?= time() ?>"></script>