<?php
$riskColors = ['low'=>'success','medium'=>'info','high'=>'warning','critical'=>'danger'];
$riskLabels = ['low'=>'پایین','medium'=>'متوسط','high'=>'بالا','critical'=>'بحرانی'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-chart-line me-2"></i>ارزیابی ریسک - <?= qms_e($program['title']) ?></h4>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=show&id=<?= $program['id'] ?>" class="btn btn-outline-secondary">بازگشت</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header bg-white">ثبت / ویرایش ارزیابی واحد</div>
                <div class="card-body">
                    <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=saveRiskAssessment">
                        <input type="hidden" name="program_id" value="<?= $program['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">واحد سازمانی <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                <option value="">انتخاب کنید...</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= qms_e($d['name_fa']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">نام فرآیند</label>
                            <input type="text" name="process_name" class="form-control">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">اهمیت واحد</label>
                                <select name="importance" class="form-select">
                                    <option value="low">کم</option><option value="medium" selected>متوسط</option>
                                    <option value="high">زیاد</option><option value="critical">حیاتی</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">نتیجه ممیزی قبلی</label>
                                <select name="previous_audit_result" class="form-select">
                                    <option value="">-</option>
                                    <option value="conformity">انطباق</option><option value="ofI">فرصت بهبود</option>
                                    <option value="observation">مشاهده</option><option value="minor_nc">NC جزئی</option>
                                    <option value="major_nc">NC عمده</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">احتمال وقوع</label>
                                <select name="risk_probability" class="form-select">
                                    <option value="very_low">خیلی کم</option><option value="low">کم</option>
                                    <option value="medium" selected>متوسط</option><option value="high">زیاد</option>
                                    <option value="very_high">خیلی زیاد</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">شدت تأثیر</label>
                                <select name="risk_impact" class="form-select">
                                    <option value="very_low">خیلی کم</option><option value="low">کم</option>
                                    <option value="medium" selected>متوسط</option><option value="high">زیاد</option>
                                    <option value="very_high">خیلی زیاد</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تاریخ ممیزی قبلی (شمسی)</label>
                            <input type="text" name="previous_audit_date" class="form-control" placeholder="مثال: ۱۴۰۴/۰۵/۲۴">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">تغییرات از آخرین ممیزی / یادداشت</label>
                            <textarea name="notes" rows="2" class="form-control"></textarea>
                        </div>
                        <button class="btn btn-primary w-100">ذخیره ارزیابی</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">واحدهای ارزیابی‌شده (<?= fa_digits(count($riskAssessments)) ?>)</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>واحد</th><th class="text-center">امتیاز</th><th class="text-center">سطح</th>
                                <th class="text-center">تاریخ ممیزی قبلی</th><th class="text-end">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($riskAssessments)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">هنوز ارزیابی ثبت نشده است.</td></tr>
                        <?php else: ?>
                            <?php foreach ($riskAssessments as $ra): ?>
                            <tr>
                                <td><strong><?= qms_e($ra['department_name']) ?></strong></td>
                                <td class="text-center"><?= fa_digits($ra['risk_score']) ?></td>
                                <td class="text-center"><span class="badge bg-<?= $riskColors[$ra['risk_level']] ?>"><?= $riskLabels[$ra['risk_level']] ?></span></td>
                                <td class="text-center"><?= fa_jdate($ra['previous_audit_date']) ?></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-danger"
                                       href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=deleteRiskAssessment&id=<?= $ra['id'] ?>"
                                       onclick="return confirm('حذف شود؟');">حذف</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>