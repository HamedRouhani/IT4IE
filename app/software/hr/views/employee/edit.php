<?php
/**
 * مشابه create.php است، فقط value‌ها از $employee پر می‌شوند
 * و action به update اشاره می‌کند
 */
$emp = $employee;
?>

<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش کارمند
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ویرایش: <strong><?= hr_e(hr_full_name($emp)) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('employee', 'update', ['id' => $emp['id']]) ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات شناسایی -->
        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> اطلاعات شناسایی</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_code">کد پرسنلی <span style="color: var(--hr-danger);">*</span></label>
                        <input type="text" id="employee_code" name="employee_code"
                               class="hr-form-control"
                               value="<?= hr_e($emp['employee_code']) ?>"
                               required maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="national_id">کد ملی</label>
                        <input type="text" id="national_id" name="national_id"
                               class="hr-form-control"
                               value="<?= hr_e($emp['national_id'] ?? '') ?>"
                               maxlength="20">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="personnel_number">شماره پرسنلی</label>
                        <input type="text" id="personnel_number" name="personnel_number"
                               class="hr-form-control"
                               value="<?= hr_e($emp['personnel_number'] ?? '') ?>"
                               maxlength="50">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="first_name">نام <span style="color: var(--hr-danger);">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                               class="hr-form-control"
                               value="<?= hr_e($emp['first_name']) ?>"
                               required maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="last_name">نام خانوادگی <span style="color: var(--hr-danger);">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                               class="hr-form-control"
                               value="<?= hr_e($emp['last_name']) ?>"
                               required maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="father_name">نام پدر</label>
                        <input type="text" id="father_name" name="father_name"
                               class="hr-form-control"
                               value="<?= hr_e($emp['father_name'] ?? '') ?>"
                               maxlength="100">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="gender">جنسیت</label>
                        <select id="gender" name="gender" class="hr-form-control">
                            <option value="male" <?= $emp['gender'] === 'male' ? 'selected' : '' ?>>مرد</option>
                            <option value="female" <?= $emp['gender'] === 'female' ? 'selected' : '' ?>>زن</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="birth_date">تاریخ تولد (شمسی)</label>
                        <input type="text" id="birth_date" name="birth_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $emp['birth_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($emp['birth_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="birth_place">محل تولد</label>
                        <input type="text" id="birth_place" name="birth_place"
                               class="hr-form-control"
                               value="<?= hr_e($emp['birth_place'] ?? '') ?>"
                               maxlength="150">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="marital_status">وضعیت تأهل</label>
                        <select id="marital_status" name="marital_status" class="hr-form-control">
                            <option value="single" <?= $emp['marital_status'] === 'single' ? 'selected' : '' ?>>مجرد</option>
                            <option value="married" <?= $emp['marital_status'] === 'married' ? 'selected' : '' ?>>متأهل</option>
                            <option value="divorced" <?= $emp['marital_status'] === 'divorced' ? 'selected' : '' ?>>مطلقه</option>
                            <option value="widowed" <?= $emp['marital_status'] === 'widowed' ? 'selected' : '' ?>>بیوه</option>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="dependents_count">تعداد افراد تحت تکفل</label>
                        <input type="number" id="dependents_count" name="dependents_count"
                               class="hr-form-control"
                               value="<?= (int) $emp['dependents_count'] ?>"
                               min="0" max="20">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="military_status">وضعیت خدمت</label>
                        <select id="military_status" name="military_status" class="hr-form-control">
                            <?php
                            $milStatuses = ['not_applicable' => 'مشمول نیست', 'completed' => 'انجام شده', 'exempt' => 'معاف', 'in_progress' => 'در حال انجام'];
                            foreach ($milStatuses as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $emp['military_status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- تماس -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-phone"></i> اطلاعات تماس</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="mobile">موبایل</label>
                        <input type="text" id="mobile" name="mobile"
                               class="hr-form-control" value="<?= hr_e($emp['mobile'] ?? '') ?>" maxlength="20">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="phone">تلفن</label>
                        <input type="text" id="phone" name="phone"
                               class="hr-form-control" value="<?= hr_e($emp['phone'] ?? '') ?>" maxlength="20">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="email">ایمیل</label>
                        <input type="email" id="email" name="email"
                               class="hr-form-control" value="<?= hr_e($emp['email'] ?? '') ?>" maxlength="150">
                    </div>
                </div>
                <div class="hr-form-group">
                    <label class="hr-form-label" for="address">آدرس</label>
                    <textarea id="address" name="address" class="hr-form-control" rows="2" maxlength="500"><?= hr_e($emp['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- تحصیلی -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> اطلاعات تحصیلی</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="education_level">مقطع</label>
                        <select id="education_level" name="education_level" class="hr-form-control">
                            <?php
                            $eduLevels = ['below_diploma'=>'زیر دیپلم','diploma'=>'دیپلم','associate'=>'کاردانی','bachelor'=>'کارشناسی','master'=>'کارشناسی ارشد','phd'=>'دکتری','postdoc'=>'پسا دکتری'];
                            foreach ($eduLevels as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $emp['education_level'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="field_of_study">رشته</label>
                        <input type="text" id="field_of_study" name="field_of_study"
                               class="hr-form-control" value="<?= hr_e($emp['field_of_study'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="university">دانشگاه</label>
                        <input type="text" id="university" name="university"
                               class="hr-form-control" value="<?= hr_e($emp['university'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="graduation_year">سال فارغ‌التحصیلی</label>
                        <input type="number" id="graduation_year" name="graduation_year"
                               class="hr-form-control" value="<?= hr_e($emp['graduation_year'] ?? '') ?>" min="1300" max="1450">
                    </div>
                </div>
            </div>
        </div>

        <!-- شغلی -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-briefcase"></i> اطلاعات شغلی</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="department_id">دپارتمان</label>
                        <select id="department_id" name="department_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>" <?= (int) $emp['department_id'] === (int) $d['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="position_id">پست</label>
                        <select id="position_id" name="position_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_positions as $p): ?>
                                <option value="<?= (int) $p['id'] ?>" <?= (int) $emp['position_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($p['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="grade_id">طبقه</label>
                        <select id="grade_id" name="grade_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_grades as $g): ?>
                                <option value="<?= (int) $g['id'] ?>" <?= (int) $emp['grade_id'] === (int) $g['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($g['code']) ?> — <?= hr_e($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="manager_id">مدیر مستقیم</label>
                    <select id="manager_id" name="manager_id" class="hr-form-control">
                        <option value="">— بدون مدیر —</option>
                        <?php foreach ($hr_managers as $m): ?>
                            <option value="<?= (int) $m['id'] ?>" <?= (int) $emp['manager_id'] === (int) $m['id'] ? 'selected' : '' ?>>
                                <?= hr_e($m['full_name']) ?> (<?= hr_e($m['employee_code']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="hire_date">تاریخ استخدام (شمسی)</label>
                        <input type="text" id="hire_date" name="hire_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $emp['hire_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($emp['hire_date'], 'Y/m/d')) : '' ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="contract_type">نوع قرارداد</label>
                        <select id="contract_type" name="contract_type" class="hr-form-control">
                            <?php
                            $ctypes = ['permanent'=>'دائمی','fixed_term'=>'مدت معین','project'=>'پروژه‌ای','intern'=>'کارآموز','consultant'=>'مشاور'];
                            foreach ($ctypes as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $emp['contract_type'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employment_status">وضعیت</label>
                        <select id="employment_status" name="employment_status" class="hr-form-control">
                            <?php
                            $estatuses = ['active'=>'شاغل','on_leave'=>'مرخصی','suspended'=>'تعلیق','terminated'=>'خاتمه همکاری','retired'=>'بازنشسته'];
                            foreach ($estatuses as $k => $v):
                            ?>
                                <option value="<?= $k ?>" <?= $emp['employment_status'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="contract_start_date">شروع قرارداد (شمسی)</label>
                        <input type="text" id="contract_start_date" name="contract_start_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $emp['contract_start_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($emp['contract_start_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="contract_end_date">پایان قرارداد (شمسی)</label>
                        <input type="text" id="contract_end_date" name="contract_end_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $emp['contract_end_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($emp['contract_end_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="probation_end_date">پایان دوره آزمایشی (شمسی)</label>
                        <input type="text" id="probation_end_date" name="probation_end_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= $emp['probation_end_date'] ? hr_e(\App\Helpers\DateHelper::toJalali($emp['probation_end_date'], 'Y/m/d')) : '' ?>"
                               maxlength="10" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- بانکی -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-university"></i> بانکی و بیمه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="bank_name">بانک</label>
                        <input type="text" id="bank_name" name="bank_name" class="hr-form-control" value="<?= hr_e($emp['bank_name'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="bank_account">شماره حساب</label>
                        <input type="text" id="bank_account" name="bank_account" class="hr-form-control" value="<?= hr_e($emp['bank_account'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="iban">شبا</label>
                        <input type="text" id="iban" name="iban" class="hr-form-control" value="<?= hr_e($emp['iban'] ?? '') ?>" maxlength="50">
                    </div>
                </div>
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="card_number">شماره کارت</label>
                        <input type="text" id="card_number" name="card_number" class="hr-form-control" value="<?= hr_e($emp['card_number'] ?? '') ?>" maxlength="30">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="insurance_number">شماره بیمه</label>
                        <input type="text" id="insurance_number" name="insurance_number" class="hr-form-control" value="<?= hr_e($emp['insurance_number'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="insurance_type">نوع بیمه</label>
                        <input type="text" id="insurance_type" name="insurance_type" class="hr-form-control" value="<?= hr_e($emp['insurance_type'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="tax_code">کد مالیاتی</label>
                        <input type="text" id="tax_code" name="tax_code" class="hr-form-control" value="<?= hr_e($emp['tax_code'] ?? '') ?>" maxlength="50">
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> توضیحات</h3></div>
            <div class="card-body">
                <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($emp['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('employee', 'show', ['id' => $emp['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>