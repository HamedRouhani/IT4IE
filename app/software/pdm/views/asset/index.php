<?php
/**
 * PdM Analyzer - لیست دارایی‌ها
 * مسیر: app/software/pdm/views/asset/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-cogs"></i> تجهیزات و دارایی‌ها
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong> دارایی
                — فعال: <strong><?= pdm_num($stats['active']) ?></strong>
                — بحرانی: <strong style="color: var(--pdm-danger);"><?= pdm_num($stats['critical']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('asset', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> افزودن دارایی
        </a>
    </div>

    <!-- پیام Flash -->
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
            <form method="GET" action="<?= pdm_url('asset') ?>">
                <input type="hidden" name="controller" value="asset">
                <input type="hidden" name="action" value="index">

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">جستجو</label>
                        <input type="text"
                               name="q"
                               class="pdm-form-control"
                               placeholder="نام، کد یا سریال..."
                               value="<?= pdm_e($filters['q']) ?>">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">وضعیت</label>
                        <select name="status" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                            <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                            <option value="maintenance" <?= $filters['status'] === 'maintenance' ? 'selected' : '' ?>>در تعمیر</option>
                            <option value="retired" <?= $filters['status'] === 'retired' ? 'selected' : '' ?>>بازنشسته</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">بحرانیت</label>
                        <select name="criticality" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <option value="critical" <?= $filters['criticality'] === 'critical' ? 'selected' : '' ?>>بحرانی</option>
                            <option value="high" <?= $filters['criticality'] === 'high' ? 'selected' : '' ?>>بالا</option>
                            <option value="medium" <?= $filters['criticality'] === 'medium' ? 'selected' : '' ?>>متوسط</option>
                            <option value="low" <?= $filters['criticality'] === 'low' ? 'selected' : '' ?>>پایین</option>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">مکان</label>
                        <select name="location_id" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($pdm_locations as $loc): ?>
                                <option value="<?= (int) $loc['id'] ?>"
                                    <?= (int) $filters['location_id'] === (int) $loc['id'] ? 'selected' : '' ?>>
                                    <?= str_repeat('— ', (int) ($loc['level'] ?? 0)) . pdm_e($loc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label">دسته</label>
                        <select name="category_id" class="pdm-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($pdm_categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (int) $filters['category_id'] === (int) $cat['id'] ? 'selected' : '' ?>>
                                    <?= str_repeat('— ', (int) ($cat['level'] ?? 0)) . pdm_e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group" style="display: flex; align-items: flex-end;">
                        <div class="pdm-flex pdm-gap-2">
                            <button type="submit" class="btn-pdm-primary">
                                <i class="fas fa-search"></i> اعمال فیلتر
                            </button>
                            <a href="<?= pdm_url('asset') ?>" class="btn-pdm-outline">
                                <i class="fas fa-redo"></i> پاک کردن
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول دارایی‌ها -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست دارایی‌ها
                <span class="pdm-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= pdm_num(count($assets)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($assets)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-cogs"></i>
                <h4>هیچ دارایی‌ای یافت نشد</h4>
                <p>اولین دارایی خود را ایجاد کنید.</p>
                <a href="<?= pdm_url('asset', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> ایجاد اولین دارایی
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام</th>
                            <th>مکان</th>
                            <th>دسته</th>
                            <th>سازنده / مدل</th>
                            <th>بحرانیت</th>
                            <th>وضعیت</th>
                            <th style="width: 180px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $asset): ?>
                            <tr>
                                <td><code><?= pdm_e($asset['asset_code']) ?></code></td>
                                <td>
                                    <a href="<?= pdm_url('asset', 'show', ['id' => $asset['id']]) ?>"
                                       style="color: var(--pdm-primary-dark); font-weight: 600;">
                                        <?= pdm_e($asset['name']) ?>
                                    </a>
                                </td>
                                <td><?= pdm_e($asset['location_name'] ?? '—') ?></td>
                                <td><?= pdm_e($asset['category_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($asset['manufacturer']) || !empty($asset['model'])): ?>
                                        <?= pdm_e(trim(($asset['manufacturer'] ?? '') . ' / ' . ($asset['model'] ?? ''), ' /')) ?>
                                    <?php else: ?>
                                        <span class="pdm-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="pdm-criticality-badge <?= pdm_criticality_class($asset['criticality']) ?>">
                                        <?= pdm_criticality_label($asset['criticality']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="pdm-status-badge pdm-status-<?= pdm_e($asset['status']) ?>">
                                        <?= pdm_status_label($asset['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('asset', 'show', ['id' => $asset['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= pdm_url('asset', 'edit', ['id' => $asset['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= pdm_url('asset', 'delete', ['id' => $asset['id']]) ?>"
                                           class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                           data-message="آیا از حذف دارایی '<?= pdm_e($asset['name']) ?>' اطمینان دارید؟"
                                           title="حذف"
                                           style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
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