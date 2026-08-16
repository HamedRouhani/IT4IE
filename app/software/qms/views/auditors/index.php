<?php
/**
 * ویو: لیست ممیزان
 * متغیرهای ورودی از کنترلر: $auditors (همراه user_name, lead_count, item_count)
 */
?>
<div class="container-fluid py-4">

    <!-- سربرگ -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-user-tie me-2 text-primary"></i>ممیزان</h4>
            <p class="text-muted small mb-0">مدیریت پروفایل ممیزان و سرممیزان بر اساس الزام صلاحیت ISO 19011</p>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=auditors&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>ثبت ممیز جدید
        </a>
    </div>

    <!-- جدول ممیزان -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نقش</th>
                        <th>گواهینامه‌ها</th>
                        <th>تجربه (سال)</th>
                        <th>ممیزی‌ها</th>
                        <th>سرممیزی‌ها</th>
                        <th>وضعیت</th>
                        <th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($auditors)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                هنوز ممیزی ثبت نشده است.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($auditors as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($a['full_name']) ?></strong>
                                <?php if (!empty($a['qualification'])): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($a['qualification']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($a['lead_auditor']): ?>
                                    <span class="badge bg-primary"><i class="fas fa-star me-1"></i>سرممیز</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">ممیز</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($a['iso_9001_certified']): ?>
                                    <span class="badge bg-success">ISO 9001</span>
                                <?php endif; ?>
                                <?php if ($a['iso_19011_certified']): ?>
                                    <span class="badge bg-info text-dark">ISO 19011</span>
                                <?php endif; ?>
                                <?php if (!$a['iso_9001_certified'] && !$a['iso_19011_certified']): ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$a['experience_years'] ?></td>
                            <td><?= (int)$a['audit_count'] ?></td>
                            <td><?= (int)$a['lead_count'] ?></td>
                            <td>
                                <?php if ($a['is_active']): ?>
                                    <span class="badge bg-success">فعال</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">غیرفعال</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= CURRENT_MODULE_URL ?>?controller=auditors&action=edit&id=<?= $a['id'] ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>ویرایش
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>