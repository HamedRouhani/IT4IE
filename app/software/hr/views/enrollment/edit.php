<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش ثبت‌نام
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ثبت‌نام #<?= hr_num($enrollment['id']) ?>
            </p>
        </div>
        <a href="<?= hr_url('enrollment', 'show', ['id' => $enrollment['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('enrollment', 'update', ['id' => $enrollment['id']]) ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات ثبت‌نام</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="training_id">دوره آموزشی</label>
                        <select id="training_id" name="training_id" class="hr-form-control" required>
                            <?php foreach ($hr_trainings as $t): ?>
                                <option value="<?= (int) $t['id'] ?>"
                                    <?= (int) $enrollment['training_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($t['title']) ?>
                                    <?php if (!empty($t['code'])): ?>
                                        (<?= hr_e($t['code']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_id">کارمند</label>
                        <select id="employee_id" name="employee_id" class="hr-form-control" required>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $enrollment['employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="enrolled_date">تاریخ ثبت‌نام (شمسی)</label>
                        <input type="text" id="enrolled_date" name="enrolled_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_date($enrollment['enrolled_date'], 'Y/m/d')) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $enrollment['status'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- نتایج -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> نتایج و ارزیابی</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="attendance_percent">درصد حضور (۰-۱۰۰)</label>
                        <input type="number" id="attendance_percent" name="attendance_percent"
                               class="hr-form-control" min="0" max="100" step="1"
                               value="<?= hr_e($enrollment['attendance_percent'] ?? '') ?>">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="score">امتیاز نهایی</label>
                        <input type="number" id="score" name="score"
                               class="hr-form-control" min="0" max="100" step="0.01"
                               value="<?= hr_e($enrollment['score'] ?? '') ?>">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="rating">رتبه کیفی</label>
                        <select id="rating" name="rating" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($ratingOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $enrollment['rating'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="completed_at">تاریخ اتمام (شمسی)</label>
                        <input type="text" id="completed_at" name="completed_at"
                               class="hr-form-control hr-datepicker"
                               value="<?= $enrollment['completed_at'] ? hr_e(hr_date($enrollment['completed_at'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="certificate_issued">صدور گواهی‌نامه</label>
                        <select id="certificate_issued" name="certificate_issued" class="hr-form-control">
                            <option value="0" <?= empty($enrollment['certificate_issued']) ? 'selected' : '' ?>>خیر</option>
                            <option value="1" <?= !empty($enrollment['certificate_issued']) ? 'selected' : '' ?>>بله</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="certificate_number">شماره گواهی</label>
                        <input type="text" id="certificate_number" name="certificate_number"
                               class="hr-form-control" maxlength="100"
                               value="<?= hr_e($enrollment['certificate_number'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- یادداشت‌ها -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> یادداشت‌ها</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="feedback">بازخورد کارمند</label>
                        <textarea id="feedback" name="feedback" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($enrollment['feedback'] ?? '') ?></textarea>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_notes">یادداشت کارمند</label>
                        <textarea id="employee_notes" name="employee_notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($enrollment['employee_notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="manager_notes">یادداشت مدیر</label>
                    <textarea id="manager_notes" name="manager_notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($enrollment['manager_notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره تغییرات</button>
            <a href="<?= hr_url('enrollment', 'show', ['id' => $enrollment['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>