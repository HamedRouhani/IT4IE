<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش رکورد تاریخچه
            </h2>
        </div>
        <a href="<?= hr_url('employee', 'show', ['id' => $history['employee_id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('employee_history', 'update', ['id' => $history['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_id">کارمند</label>
                        <select id="employee_id" name="employee_id" class="hr-form-control" required>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $history['employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?> (<?= hr_e($e['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="change_type">نوع تغییر</label>
                        <select id="change_type" name="change_type" class="hr-form-control">
                            <?php foreach ($changeTypes as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $history['change_type'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="change_date">تاریخ تغییر (شمسی)</label>
                        <input type="text" id="change_date" name="change_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $history['change_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($history['change_date'], 'Y/m/d')) : '' ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="from_value">مقدار قبلی</label>
                        <input type="text" id="from_value" name="from_value"
                               class="hr-form-control"
                               value="<?= hr_e($history['from_value'] ?? '') ?>" maxlength="500">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="to_value">مقدار جدید</label>
                        <input type="text" id="to_value" name="to_value"
                               class="hr-form-control"
                               value="<?= hr_e($history['to_value'] ?? '') ?>" maxlength="500">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="reason">دلیل تغییر</label>
                    <textarea id="reason" name="reason"
                              class="hr-form-control" rows="2"
                              maxlength="1000"><?= hr_e($history['reason'] ?? '') ?></textarea>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="document_ref">شماره نامه</label>
                    <input type="text" id="document_ref" name="document_ref"
                           class="hr-form-control"
                           value="<?= hr_e($history['document_ref'] ?? '') ?>" maxlength="100">
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('employee', 'show', ['id' => $history['employee_id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>