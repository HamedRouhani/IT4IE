<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش نیاز استخدامی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ویرایش: <code><?= hr_e($recruitment['request_number']) ?></code>
            </p>
        </div>
        <a href="<?= hr_url('recruitment', 'show', ['id' => $recruitment['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('recruitment', 'update', ['id' => $recruitment['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="request_number">شماره درخواست</label>
                        <input type="text" id="request_number" name="request_number"
                               class="hr-form-control"
                               value="<?= hr_e($recruitment['request_number']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="opened_date">تاریخ باز شدن (شمسی)</label>
                        <input type="text" id="opened_date" name="opened_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $recruitment['opened_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($recruitment['opened_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="target_date">تاریخ هدف (شمسی)</label>
                        <input type="text" id="target_date" name="target_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $recruitment['target_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($recruitment['target_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="title">عنوان آگهی</label>
                    <input type="text" id="title" name="title"
                           class="hr-form-control"
                           value="<?= hr_e($recruitment['title']) ?>"
                           required maxlength="200">
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="position_id">پست مرتبط</label>
                        <select id="position_id" name="position_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_positions as $p): ?>
                                <option value="<?= (int) $p['id'] ?>"
                                    <?= (int) $recruitment['position_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($p['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="department_id">دپارتمان</label>
                        <select id="department_id" name="department_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>"
                                    <?= (int) $recruitment['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="headcount">تعداد مورد نیاز</label>
                        <input type="number" id="headcount" name="headcount"
                               class="hr-form-control"
                               value="<?= (int) $recruitment['headcount'] ?>"
                               min="1" max="999">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="2000"><?= hr_e($recruitment['description'] ?? '') ?></textarea>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="requirements">شرایط احراز</label>
                    <textarea id="requirements" name="requirements"
                              class="hr-form-control" rows="4"
                              maxlength="3000"><?= hr_e($recruitment['requirements'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-cog"></i> شرایط و وضعیت</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employment_type">نوع استخدام</label>
                        <select id="employment_type" name="employment_type" class="hr-form-control">
                            <?php
                            $etypes = ['full_time'=>'تمام‌وقت','part_time'=>'پاره‌وقت','contract'=>'قراردادی','intern'=>'کارآموز'];
                            foreach ($etypes as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $recruitment['employment_type'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="hr-form-control">
                            <?php foreach ($priorityOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $recruitment['priority'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $recruitment['status'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_salary">حداقل حقوق</label>
                        <input type="text" id="min_salary" name="min_salary"
                               class="hr-form-control"
                               value="<?= hr_e($recruitment['min_salary'] ?? '') ?>"
                               inputmode="numeric">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="max_salary">حداکثر حقوق</label>
                        <input type="text" id="max_salary" name="max_salary"
                               class="hr-form-control"
                               value="<?= hr_e($recruitment['max_salary'] ?? '') ?>"
                               inputmode="numeric">
                    </div>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('recruitment', 'show', ['id' => $recruitment['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>