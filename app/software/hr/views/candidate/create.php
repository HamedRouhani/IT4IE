<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-plus"></i> افزودن متقاضی
            </h2>
        </div>
        <a href="<?= hr_url('candidate') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('candidate', 'store') ?>" enctype="multipart/form-data">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> اطلاعات پایه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="candidate_code">کد متقاضی</label>
                        <input type="text" id="candidate_code" name="candidate_code"
                               class="hr-form-control"
                               value="<?= hr_e($suggested_code) ?>"
                               maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recruitment_id">آگهی مرتبط</label>
                        <select id="recruitment_id" name="recruitment_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"
                                    <?= (int) $preselectedRecruitmentId === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($r['request_number']) ?> — <?= hr_e($r['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="applied_date">تاریخ درخواست (شمسی)</label>
                        <input type="text" id="applied_date" name="applied_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_today()) ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="first_name">
                            نام <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name"
                               class="hr-form-control" required maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="last_name">
                            نام خانوادگی <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name"
                               class="hr-form-control" required maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="national_id">کد ملی</label>
                        <input type="text" id="national_id" name="national_id"
                               class="hr-form-control" maxlength="20">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="gender">جنسیت</label>
                        <select id="gender" name="gender" class="hr-form-control">
                            <option value="male">مرد</option>
                            <option value="female">زن</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="birth_date">تاریخ تولد (شمسی)</label>
                        <input type="text" id="birth_date" name="birth_date"
                               class="hr-form-control hr-datepicker"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="source">منبع آگهی</label>
                        <select id="source" name="source" class="hr-form-control">
                            <?php foreach ($sourceOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"><?= hr_e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- تماس -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-phone"></i> تماس</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="mobile">موبایل</label>
                        <input type="text" id="mobile" name="mobile" class="hr-form-control" maxlength="20">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="email">ایمیل</label>
                        <input type="email" id="email" name="email" class="hr-form-control" maxlength="150">
                    </div>
                </div>
                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address" class="hr-form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>
        </div>

        <!-- تحصیلات و سابقه -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> تحصیلات و سابقه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="education_level">مقطع</label>
                        <select id="education_level" name="education_level" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <option value="diploma">دیپلم</option>
                            <option value="associate">کاردانی</option>
                            <option value="bachelor">کارشناسی</option>
                            <option value="master">کارشناسی ارشد</option>
                            <option value="phd">دکتری</option>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="field_of_study">رشته</label>
                        <input type="text" id="field_of_study" name="field_of_study" class="hr-form-control" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="university">دانشگاه</label>
                        <input type="text" id="university" name="university" class="hr-form-control" maxlength="200">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="experience_years">سابقه کار (سال)</label>
                        <input type="number" id="experience_years" name="experience_years"
                               class="hr-form-control" value="0" min="0" max="60">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_company">شرکت فعلی</label>
                        <input type="text" id="current_company" name="current_company" class="hr-form-control" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_position">سمت فعلی</label>
                        <input type="text" id="current_position" name="current_position" class="hr-form-control" maxlength="200">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="expected_salary">حقوق مورد انتظار (ریال)</label>
                        <input type="text" id="expected_salary" name="expected_salary"
                               class="hr-form-control" inputmode="numeric">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="portfolio_url">لینک نمونه‌کار</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" class="hr-form-control" maxlength="500">
                    </div>
                </div>
            </div>
        </div>

        <!-- وضعیت -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-flag"></i> وضعیت و توضیحات</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $key === 'new' ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="rating">امتیاز اولیه (۰-۵)</label>
                        <input type="number" id="rating" name="rating"
                               class="hr-form-control" min="0" max="5" step="0.1">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= hr_url('candidate') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>