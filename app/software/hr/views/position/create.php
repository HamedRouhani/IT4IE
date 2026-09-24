<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن پست جدید
            </h2>
        </div>
        <a href="<?= hr_url('position') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('position', 'store') ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="title">
                            عنوان پست <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                               class="hr-form-control"
                               placeholder="مثال: کارشناس ارشد منابع انسانی"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">کد پست</label>
                        <input type="text" id="code" name="code"
                               class="hr-form-control"
                               placeholder="مثال: HR-MGR-001" maxlength="50">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="department_id">دپارتمان</label>
                        <select id="department_id" name="department_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>">
                                    <?= hr_e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="grade_id">طبقه شغلی</label>
                        <select id="grade_id" name="grade_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_grades as $g): ?>
                                <option value="<?= (int) $g['id'] ?>">
                                    <?= hr_e($g['code']) ?> — <?= hr_e($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="parent_position_id">پست بالادستی</label>
                    <select id="parent_position_id" name="parent_position_id" class="hr-form-control">
                        <option value="">— بدون والد —</option>
                        <?php foreach ($hr_parents as $p): ?>
                            <option value="<?= (int) $p['id'] ?>">
                                <?= hr_e($p['title']) ?>
                                <?php if (!empty($p['department_name'])): ?>
                                    (<?= hr_e($p['department_name']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- نوع و ظرفیت -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cog"></i> نوع و ظرفیت</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="position_type">نوع پست</label>
                        <select id="position_type" name="position_type" class="hr-form-control">
                            <option value="permanent">دائمی</option>
                            <option value="contract">قراردادی</option>
                            <option value="temporary">موقت</option>
                            <option value="intern">کارآموز</option>
                            <option value="consultant">مشاور</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employment_type">نوع استخدام</label>
                        <select id="employment_type" name="employment_type" class="hr-form-control">
                            <option value="full_time">تمام‌وقت</option>
                            <option value="part_time">پاره‌وقت</option>
                            <option value="remote">دورکاری</option>
                            <option value="hybrid">ترکیبی</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="headcount">ظرفیت (تعداد نفرات)</label>
                        <input type="number" id="headcount" name="headcount"
                               class="hr-form-control" value="1" min="1" max="999">
                    </div>
                </div>
            </div>
        </div>

        <!-- شرایط احراز -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap"></i> شرایط احراز</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_education">حداقل تحصیلات</label>
                        <input type="text" id="min_education" name="min_education"
                               class="hr-form-control"
                               placeholder="مثال: کارشناسی"
                               maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_experience_years">حداقل سابقه (سال)</label>
                        <input type="number" id="min_experience_years" name="min_experience_years"
                               class="hr-form-control" value="0" min="0" max="50">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label">ویژگی‌ها</label>
                    <div class="hr-flex hr-gap-3" style="flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_managerial" value="1">
                            <span>پست مدیریتی</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_critical" value="1">
                            <span>پست کلیدی (نیاز به جانشین‌پروری)</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            <span>فعال</span>
                        </label>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="2000"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= hr_url('position') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>