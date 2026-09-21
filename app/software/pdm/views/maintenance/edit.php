<?php
/**
 * PdM Analyzer - فرم ویرایش برنامه نگهداری
 * مسیر: app/software/pdm/views/maintenance/edit.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش برنامه نگهداری
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ویرایش: <strong><?= pdm_e($plan['title']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('maintenance') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('maintenance', 'update', ['id' => $plan['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات پایه
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="title">
                        عنوان برنامه <span style="color: var(--pdm-danger);">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           class="pdm-form-control"
                           value="<?= pdm_e($plan['title']) ?>"
                           required maxlength="150">
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_id">
                            دارایی <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <select id="asset_id" name="asset_id" class="pdm-form-control" required>
                            <?php foreach ($pdm_assets as $asset): ?>
                                <option value="<?= (int) $asset['id'] ?>"
                                    <?= (int) $plan['asset_id'] === (int) $asset['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($asset['asset_code']) ?> — <?= pdm_e($asset['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="maintenance_type_id">نوع نگهداری</label>
                        <select id="maintenance_type_id" name="maintenance_type_id" class="pdm-form-control">
                            <?php foreach ($pdm_types as $type): ?>
                                <option value="<?= (int) $type['id'] ?>"
                                    <?= (int) $plan['maintenance_type_id'] === (int) $type['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($type['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"><?= pdm_e($plan['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i>
                    فرکانس اجرا
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="frequency_value">هر</label>
                        <input type="number" id="frequency_value" name="frequency_value"
                               class="pdm-form-control"
                               value="<?= (int) $plan['frequency_value'] ?>"
                               min="1" max="9999" required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="frequency_unit">واحد</label>
                        <select id="frequency_unit" name="frequency_unit" class="pdm-form-control">
                            <?php foreach ($frequencyUnits as $key => $label): ?>
                                <option value="<?= pdm_e($key) ?>"
                                    <?= $plan['frequency_unit'] === $key ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="start_date">تاریخ شروع (شمسی)</label>
                        <input type="text" id="start_date" name="start_date"
                               class="pdm-form-control pdm-datepicker"
                               value="<?= $plan['start_date'] ? pdm_e(\App\Helpers\DateHelper::toJalali($plan['start_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10"
                               autocomplete="off">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="next_execution">اجرای بعدی (شمسی)</label>
                        <input type="text" id="next_execution" name="next_execution"
                               class="pdm-form-control pdm-datepicker"
                               value="<?= $plan['next_execution'] ? pdm_e(\App\Helpers\DateHelper::toJalali($plan['next_execution'], 'Y/m/d')) : '' ?>"
                               maxlength="10"
                               autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flag"></i>
                    اولویت و وضعیت
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="pdm-form-control">
                            <?php
                            $prioOptions = ['low' => 'پایین', 'normal' => 'عادی', 'high' => 'بالا', 'urgent' => 'فوری'];
                            foreach ($prioOptions as $key => $label):
                            ?>
                                <option value="<?= $key ?>"
                                    <?= $plan['priority'] === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="pdm-form-control">
                            <option value="active" <?= $plan['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                            <option value="inactive" <?= $plan['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= pdm_url('maintenance') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/pdm.js?v=<?= time() ?>"></script>