<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش مقدار
            </h2>
        </div>
        <a href="<?= hr_url('kpi', 'values') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('kpi', 'updateValue', ['id' => $value['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar"></i> اطلاعات مقدار</h3></div>
            <div class="card-body">

                <div class="hr-form-group">
                    <label class="hr-form-label" for="kpi_id">شاخص</label>
                    <select id="kpi_id" name="kpi_id" class="hr-form-control" required>
                        <?php foreach ($hr_kpis as $k): ?>
                            <option value="<?= (int) $k['id'] ?>"
                                <?= (int) $value['kpi_id'] === (int) $k['id'] ? 'selected' : '' ?>>
                                <?= hr_e($k['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="period">دوره</label>
                        <input type="text" id="period" name="period" class="hr-form-control"
                               value="<?= hr_e($value['period']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="period_date">تاریخ دوره (شمسی)</label>
                        <input type="text" id="period_date" name="period_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_date($value['period_date'], 'Y/m/d')) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="value">مقدار</label>
                        <input type="text" id="value" name="value"
                               class="hr-form-control hr-money-input"
                               inputmode="decimal"
                               value="<?= hr_e($value['value']) ?>">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="target_value">مقدار هدف</label>
                        <input type="text" id="target_value" name="target_value"
                               class="hr-form-control hr-money-input"
                               inputmode="decimal"
                               value="<?= hr_e($value['target_value'] ?? '') ?>">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($value['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره تغییرات</button>
            <a href="<?= hr_url('kpi', 'values') ?>" class="btn-hr-outline"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>