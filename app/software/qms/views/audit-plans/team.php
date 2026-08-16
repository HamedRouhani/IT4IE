<?php
$roles = [
    'lead_auditor' => 'سرممیز',
    'auditor'      => 'ممیز',
    'trainee'      => 'ممیز در حال آموزش',
    'expert'       => 'کارشناس فنی'
];
$badges = [
    'lead_auditor' => 'bg-primary',
    'auditor'      => 'bg-info',
    'trainee'      => 'bg-secondary',
    'expert'       => 'bg-warning text-dark'
];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-users me-2"></i>تیم ممیزی: <?= htmlspecialchars($plan['title']) ?></h4>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=show&id=<?= $plan['id'] ?>" class="btn btn-outline-secondary btn-sm">بازگشت به برنامه</a>
    </div>

    <!-- فرم افزودن عضو -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">افزودن عضو به تیم</div>
        <div class="card-body">
            <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=addTeamMember&id=<?= $plan['id'] ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">ممیز</label>
                    <select name="auditor_id" class="form-select" required>
                        <option value="">انتخاب کنید...</option>
                        <?php foreach ($auditors as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= htmlspecialchars($a['full_name']) ?><?= $a['lead_auditor'] ? ' (دارای صلاحیت سرممیزی)' : '' ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">نقش در تیم</label>
                    <select name="role" class="form-select">
                        <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= $key ?>"><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">بندهای محول‌شده</label>
                    <input type="text" name="assigned_clauses" class="form-control" placeholder="مثال: 4.4, 6.1, 7.1">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">افزودن</button>
                </div>
            </form>
        </div>
    </div>

    <!-- لیست اعضای تیم -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">اعضای تیم (<?= count($team) ?> نفر)</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>نام</th>
                        <th>نقش</th>
                        <th>بندهای محول‌شده</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($team)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">هنوز عضوی به تیم اضافه نشده است.</td></tr>
                    <?php else: ?>
                        <?php foreach ($team as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['full_name']) ?></td>
                            <td><span class="badge <?= $badges[$m['role']] ?>"><?= $roles[$m['role']] ?></span></td>
                            <td><?= htmlspecialchars($m['assigned_clauses'] ?? '-') ?></td>
                            <td class="text-end">
                                <a href="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=removeTeamMember&id=<?= $m['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('این عضو از تیم حذف شود؟');">حذف</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>