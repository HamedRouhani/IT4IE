<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش تماس اضطراری
            </h2>
        </div>
        <a href="<?= hr_url('employee', 'show', ['id' => $contact['employee_id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('employee_contact', 'update', ['id' => $contact['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات تماس</h3></div>
            <div class="card-body">
                <div class="hr-form-group">
                    <label class="hr-form-label" for="employee_id">کارمند</label>
                    <select id="employee_id" name="employee_id" class="hr-form-control" required>
                        <?php foreach ($hr_employees as $e): ?>
                            <option value="<?= (int) $e['id'] ?>"
                                <?= (int) $contact['employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                <?= hr_e($e['full_name']) ?> (<?= hr_e($e['employee_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="contact_name">نام مخاطب</label>
                        <input type="text" id="contact_name" name="contact_name"
                               class="hr-form-control"
                               value="<?= hr_e($contact['contact_name']) ?>"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="relation">نسبت</label>
                        <input type="text" id="relation" name="relation"
                               class="hr-form-control"
                               value="<?= hr_e($contact['relation'] ?? '') ?>"
                               maxlength="100">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="mobile">موبایل</label>
                        <input type="text" id="mobile" name="mobile"
                               class="hr-form-control"
                               value="<?= hr_e($contact['mobile'] ?? '') ?>"
                               maxlength="20">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="phone">تلفن</label>
                        <input type="text" id="phone" name="phone"
                               class="hr-form-control"
                               value="<?= hr_e($contact['phone'] ?? '') ?>"
                               maxlength="20">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address"
                              class="hr-form-control" rows="2"
                              maxlength="500"><?= hr_e($contact['address'] ?? '') ?></textarea>
                </div>

                <div class="hr-form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_primary" value="1"
                            <?= $contact['is_primary'] ? 'checked' : '' ?>>
                        <span>مخاطب اصلی</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('employee', 'show', ['id' => $contact['employee_id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>