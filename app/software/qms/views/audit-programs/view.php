<?php
$riskColors = ['low'=>'success','medium'=>'info','high'=>'warning','critical'=>'danger'];
$riskLabels = ['low'=>'پایین','medium'=>'متوسط','high'=>'بالا','critical'=>'بحرانی'];
$freqLabels = ['quarterly'=>'فصلی','semi_annual'=>'شش‌ماهه','annual'=>'سالانه','biennial'=>'دوسالانه'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-file-alt me-2"></i><?= qms_e($program['title']) ?>
            <span class="badge bg-secondary ms-2">سال <?= fa_digits($program['year']) ?></span>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=riskAssessment&id=<?= $program['id'] ?>" class="btn btn-warning"><i class="fas fa-chart-line me-1"></i>ارزیابی ریسک</a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=edit&id=<?= $program['id'] ?>" class="btn btn-outline-secondary">ویرایش</a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms" class="btn btn-outline-secondary">بازگشت</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted">معیار</small><div><?= qms_e($program['criteria'] ?: 'ISO 9001:2015') ?></div></div>
                <div class="col-md-4"><small class="text-muted">روش فراوانی</small><div><?= $program['frequency_method']==='risk_based'?'مبتنی بر ریسک':($program['frequency_method']==='fixed'?'ثابت':'ترکیبی') ?></div></div>
                <div class="col-md-4"><small class="text-muted">دامنه</small><div><?= qms_e($program['scope'] ?: '-') ?></div></div>
                <?php if ($program['objectives']): ?><div class="col-12"><small class="text-muted">اهداف</small><div><?= nl2br(qms_e($program['objectives'])) ?></div></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">ارزیابی ریسک واحدها (<?= fa_digits(count($riskAssessments)) ?>)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>واحد</th><th class="text-center">امتیاز</th><th class="text-center">سطح ریسک</th>
                        <th class="text-center">فراوانی پیشنهادی</th><th class="text-center">تاریخ ممیزی قبلی</th>
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
                        <td class="text-center"><?= $freqLabels[$ra['recommended_frequency']] ?? '-' ?></td>
                        <td class="text-center"><?= fa_jdate($ra['previous_audit_date']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>