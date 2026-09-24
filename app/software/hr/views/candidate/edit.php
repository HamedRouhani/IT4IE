<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش متقاضی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ویرایش: <strong><?= hr_e(trim($candidate['first_name'] . ' ' . $candidate['last_name'])) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('candidate', 'show', ['id' => $candidate['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('candidate', 'update', ['id' => $candidate['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> اطلاعات پایه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="candidate_code">کد متقاضی</label>
                        <input type="text" id="candidate_code" name="candidate_code"
                               class="hr-form-control"
                               value="<?= hr_e($candidate['candidate_code'] ?? '') ?>"
                               maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recruitment_id">آگهی مرتبط</label>
                        <select id="recruitment_id" name="recruitment_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"
                                    <?= (int) $candidate['recruitment_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($r['request_number']) ?> — <?= hr_e($r['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="applied_date">تاریخ درخواست (شمسی)</label>
                        <input type="text" id="applied_date" name="applied_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $candidate['applied_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($candidate['applied_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="first_name">نام</label>
                        <input type="text" id="first_name" name="first_name"
                               class="hr-form-control" value="<?= hr_e($candidate['first_name']) ?>" required maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="last_name">نام خانوادگی</label>
                        <input type="text" id="last_name" name="last_name"
                               class="hr-form-control" value="<?= hr_e($candidate['last_name']) ?>" required maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="national_id">کد ملی</label>
                        <input type="text" id="national_id" name="national_id"
                               class="hr-form-control" value="<?= hr_e($candidate['national_id'] ?? '') ?>" maxlength="20">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="gender">جنسیت</label>
                        <select id="gender" name="gender" class="hr-form-control">
                            <option value="male" <?= $candidate['gender'] === 'male' ? 'selected' : '' ?>>مرد</option>
                            <option value="female" <?= $candidate['gender'] === 'female' ? 'selected' : '' ?>>زن</option>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="birth_date">تاریخ تولد (شمسی)</label>
                        <input type="text" id="birth_date" name="birth_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $candidate['birth_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($candidate['birth_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="source">منبع</label>
                        <select id="source" name="source" class="hr-form-control">
                            <?php foreach ($sourceOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $candidate['source'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-phone"></i> تماس</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="mobile">موبایل</label>
                        <input type="text" id="mobile" name="mobile" class="hr-form-control"
                               value="<?= hr_e($candidate['mobile'] ?? '') ?>" maxlength="20">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="email">ایمیل</label>
                        <input type="email" id="email" name="email" class="hr-form-control"
                               value="<?= hr_e($candidate['email'] ?? '') ?>" maxlength="150">
                    </div>
                </div>
                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address" class="hr-form-control" rows="2" maxlength="500"><?= hr_e($candidate['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> تحصیلات</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="education_level">مقطع</label>
                        <select id="education_level" name="education_level" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php
                            $eduLevels = ['diploma'=>'دیپلم','associate'=>'کاردانی','bachelor'=>'کارشناسی','master'=>'کارشناسی ارشد','phd'=>'دکتری'];
                            foreach ($eduLevels as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $candidate['education_level'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="field_of_study">رشته</label>
                        <input type="text" id="field_of_study" name="field_of_study" class="hr-form-control"
                               value="<?= hr_e($candidate['field_of_study'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="university">دانشگاه</label>
                        <input type="text" id="university" name="university" class="hr-form-control"
                               value="<?= hr_e($candidate['university'] ?? '') ?>" maxlength="200">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="experience_years">سابقه (سال)</label>
                        <input type="number" id="experience_years" name="experience_years"
                               class="hr-form-control" value="<?= (int) $candidate['experience_years'] ?>" min="0" max="60">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_company">شرکت فعلی</label>
                        <input type="text" id="current_company" name="current_company" class="hr-form-control"
                               value="<?= hr_e($candidate['current_company'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_position">سمت فعلی</label>
                        <input type="text" id="current_position" name="current_position" class="hr-form-control"
                               value="<?= hr_e($candidate['current_position'] ?? '') ?>" maxlength="200">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="expected_salary">حقوق مورد انتظار (ریال)</label>
                        <input type="text" id="expected_salary" name="expected_salary" class="hr-form-control"
                               value="<?= hr_e($candidate['expected_salary'] ?? '') ?>" inputmode="numeric">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="portfolio_url">لینک نمونه‌کار</label>
                        <input type="url" id="portfolio_url" name="portfolio_url" class="hr-form-control"
                               value="<?= hr_e($candidate['portfolio_url'] ?? '') ?>" maxlength="500">
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-flag"></i> وضعیت</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $candidate['status'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="rating">امتیاز</label>
                        <input type="number" id="rating" name="rating" class="hr-form-control"
                               value="<?= hr_e($candidate['rating'] ?? '') ?>" min="0" max="5" step="0.1">
                    </div>
                </div>
                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($candidate['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('candidate', 'show', ['id' => $candidate['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>