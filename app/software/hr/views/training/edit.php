<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش دوره آموزشی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?= hr_e($training['title']) ?>
            </p>
        </div>
        <a href="<?= hr_url('training', 'show', ['id' => $training['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('training', 'update', ['id' => $training['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="title">عنوان دوره</label>
                        <input type="text" id="title" name="title" class="hr-form-control"
                               value="<?= hr_e($training['title']) ?>" required maxlength="255">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">کد</label>
                        <input type="text" id="code" name="code" class="hr-form-control"
                               value="<?= hr_e($training['code'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="training_type">نوع</label>
                        <select id="training_type" name="training_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $training['training_type'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="category">دسته‌بندی</label>
                        <input type="text" id="category" name="category" class="hr-form-control"
                               value="<?= hr_e($training['category'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="provider">ارائه‌دهنده</label>
                        <input type="text" id="provider" name="provider" class="hr-form-control"
                               value="<?= hr_e($training['provider'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="instructor">مدرس</label>
                        <input type="text" id="instructor" name="instructor" class="hr-form-control"
                               value="<?= hr_e($training['instructor'] ?? '') ?>" maxlength="200">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description" class="hr-form-control" rows="3" maxlength="3000"><?= hr_e($training['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt"></i> زمان‌بندی و مکان</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="start_date">تاریخ شروع (شمسی)</label>
                        <input type="text" id="start_date" name="start_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $training['start_date'] ? hr_e(hr_date($training['start_date'], 'Y/m/d')) : '' ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="end_date">تاریخ پایان (شمسی)</label>
                        <input type="text" id="end_date" name="end_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $training['end_date'] ? hr_e(hr_date($training['end_date'], 'Y/m/d')) : '' ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="registration_deadline">مهلت ثبت‌نام (شمسی)</label>
                        <input type="text" id="registration_deadline" name="registration_deadline"
                               class="hr-form-control hr-datepicker"
                               value="<?= $training['registration_deadline'] ? hr_e(hr_date($training['registration_deadline'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="duration_hours">مدت (ساعت)</label>
                        <input type="number" id="duration_hours" name="duration_hours"
                               class="hr-form-control" step="0.5" min="0"
                               value="<?= hr_e($training['duration_hours'] ?? '') ?>">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="delivery_mode">شیوه برگزاری</label>
                        <select id="delivery_mode" name="delivery_mode" class="hr-form-control">
                            <?php foreach ($deliveryModeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $training['delivery_mode'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="location">مکان</label>
                        <input type="text" id="location" name="location" class="hr-form-control"
                               value="<?= hr_e($training['location'] ?? '') ?>" maxlength="255">
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-users"></i> ظرفیت و هزینه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="max_participants">حداکثر شرکت‌کنندگان</label>
                        <input type="number" id="max_participants" name="max_participants"
                               class="hr-form-control" min="1" max="1000"
                               value="<?= hr_e($training['max_participants'] ?? '') ?>">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="cost_per_person">هزینه سرانه (ریال)</label>
                        <input type="text" id="cost_per_person" name="cost_per_person"
                               class="hr-form-control" inputmode="numeric"
                               value="<?= hr_e($training['cost_per_person'] ?? '') ?>">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="total_cost">هزینه کل (ریال)</label>
                        <input type="text" id="total_cost" name="total_cost"
                               class="hr-form-control" inputmode="numeric"
                               value="<?= hr_e($training['total_cost'] ?? '') ?>">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="has_certificate">صدور گواهی‌نامه</label>
                        <select id="has_certificate" name="has_certificate" class="hr-form-control">
                            <option value="0" <?= empty($training['has_certificate']) ? 'selected' : '' ?>>خیر</option>
                            <option value="1" <?= !empty($training['has_certificate']) ? 'selected' : '' ?>>بله</option>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $training['status'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره تغییرات</button>
            <a href="<?= hr_url('training', 'show', ['id' => $training['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=