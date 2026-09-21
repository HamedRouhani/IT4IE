<?php
/**
 * PdM Analyzer - لیست دستورکارها
 * مسیر: app/software/pdm/views/workorder/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-clipboard-list"></i> دستورکارها
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong>
                — باز: <strong><?= pdm_num($stats['open']) ?></strong>
                — در حال انجام: <strong><?= pdm_num($stats['in_progress']) ?></strong>
                — فوری: <strong style="color: var(--pdm-danger);"><?= pdm_num($stats['urgent']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('workorder', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> دستورکار جدید
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فیلترها -->
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                فیلتر و جستجو
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= pdm_url('workorder') ?>">
                <input type="hidden" name="controller" value="workorder">
                <input type="hidden" name="action" value="index">

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">جستجو</label>
                        <input type="text" name="q" class="pdm-form-control"
                               placeholder="شماره، عنوان یا دارایی..."
                               value="<?= pdm_e($filters['q']) ?>">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">وضعیت</label>
                        <select name="status" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>باز</option>
                            <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                            <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                            <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">اولویت</label>
                        <select name="priority" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <option value="urgent" <?= $filters['priority'] === 'urgent' ? 'selected' : '' ?>>فوری</option>
                            <option value="high" <?= $filters['priority'] === 'high' ? 'selected' : '' ?>>بالا</option>
                            <option value="normal" <?= $filters['priority'] === 'normal' ? 'selected' : '' ?>>عادی</option>
                            <option value="low" <?= $filters['priority'] === 'low' ? 'selected' : '' ?>>پایین</option>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">دارایی</label>
                        <select name="asset_id" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($pdm_assets as $a): ?>
                                <option value="<?= (int) $a['id'] ?>"
                                    <?= (int) $filters['asset_id'] === (int) $a['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($a['asset_code']) ?> — <?= pdm_e($a['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">نوع نگهداری</label>
                        <select name="maintenance_type_id" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($pdm_types as $t): ?>
                                <option value="<?= (int) $t['id'] ?>"
                                    <?= (int) $filters['maintenance_type_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                                    <?= pdm_e($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group" style="display: flex; align-items: flex-end;">
                        <div class="pdm-flex pdm-gap-2">
                            <button type="submit" class="btn-pdm-primary">
                                <i class="fas fa-search"></i> اعمال فیلتر
                            </button>
                            <a href="<?= pdm_url('workorder') ?>" class="btn-pdm-outline">
                                <i class="fas fa-redo"></i> پاک کردن
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست دستورکارها
                <span class="pdm-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= pdm_num(count($workOrders)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($workOrders)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-clipboard"></i>
                <h4>هیچ دستورکاری یافت نشد</h4>
                <p>اولین دستورکار خود را ایجاد کنید.</p>
                <a href="<?= pdm_url('workorder', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> ایجاد اولین دستورکار
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>شماره</th>
                            <th>عنوان</th>
                            <th>دارایی</th>
                            <th>نوع</th>
                            <th>اولویت</th>
                            <th>وضعیت</th>
                            <th>تاریخ برنامه</th>
                            <th style="width: 180px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($workOrders as $wo): ?>
                            <tr>
                                <td>
                                    <a href="<?= pdm_url('workorder', 'show', ['id' => $wo['id']]) ?>"
                                       style="color: var(--pdm-primary-dark); font-weight: 600;">
                                        <code><?= pdm_e($wo['wo_number']) ?></code>
                                    </a>
                                </td>
                                <td><?= pdm_e(pdm_truncate($wo['title'] ?? '—', 40)) ?></td>
                                <td>
                                    <?php if (!empty($wo['asset_name'])): ?>
                                        <?= pdm_e($wo['asset_name']) ?>
                                        <?php if (!empty($wo['asset_code'])): ?>
                                            <br><small class="pdm-text-muted">
                                                <code><?= pdm_e($wo['asset_code']) ?></code>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="pdm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= pdm_e($wo['maintenance_type_name'] ?? '—') ?></td>
                                <td>
                                    <span class="pdm-criticality-badge <?= pdm_wo_priority_class($wo['priority']) ?>">
                                        <?= pdm_wo_priority_label($wo['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="pdm-status-badge <?= pdm_wo_status_class($wo['status']) ?>">
                                        <?= pdm_wo_status_label($wo['status']) ?>
                                    </span>
                                </td>
                                <td><?= pdm_date($wo['planned_date'] ?? $wo['created_at'], 'Y/m/d') ?></td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('workorder', 'show', ['id' => $wo['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= pdm_url('workorder', 'edit', ['id' => $wo['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if (in_array($wo['status'], ['open', 'cancelled'])): ?>
                                            <a href="<?= pdm_url('workorder', 'delete', ['id' => $wo['id']]) ?>"
                                               class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                               data-message="آیا از حذف دستورکار '<?= pdm_e($wo['wo_number']) ?>' اطمینان دارید؟"
                                               title="حذف"
                                               style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>