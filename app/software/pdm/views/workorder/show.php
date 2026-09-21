<?php
/**
 * PdM Analyzer - نمایش جزئیات دستورکار
 * مسیر: app/software/pdm/views/workorder/show.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-clipboard-list"></i>
                دستورکار <code><?= pdm_e($wo['wo_number']) ?></code>
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                <span class="pdm-status-badge <?= pdm_wo_status_class($wo['status']) ?>">
                    <?= pdm_wo_status_label($wo['status']) ?>
                </span>
                <span class="pdm-criticality-badge <?= pdm_wo_priority_class($wo['priority']) ?>"
                      style="margin-right: 0.5rem;">
                    <?= pdm_wo_priority_label($wo['priority']) ?>
                </span>
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('workorder', 'edit', ['id' => $wo['id']]) ?>" class="btn-pdm-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= pdm_url('workorder') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- کارت‌های آماری -->
    <div class="stats-grid pdm-mb-4">
        <div class="pdm-stat-card">
            <small><i class="fas fa-calendar"></i> تاریخ برنامه</small>
            <h3 style="font-size: 1.1rem;">
                <?= $wo['planned_date'] ? pdm_date($wo['planned_date'], 'Y/m/d') : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card warning">
            <small><i class="fas fa-play"></i> شروع</small>
            <h3 style="font-size: 1.1rem;">
                <?= $wo['started_at'] ? pdm_date($wo['started_at'], 'Y/m/d H:i') : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card success">
            <small><i class="fas fa-check"></i> پایان</small>
            <h3 style="font-size: 1.1rem;">
                <?= $wo['completed_at'] ? pdm_date($wo['completed_at'], 'Y/m/d H:i') : '—' ?>
            </h3>
        </div>
        <div class="pdm-stat-card">
            <small><i class="fas fa-hourglass-half"></i> مدت انجام</small>
            <h3 style="font-size: 1.1rem;">
                <?= $duration !== null ? pdm_num($duration) . ' ساعت' : '—' ?>
            </h3>
        </div>
    </div>

    <div class="main-grid">
        <!-- ستون چپ -->
        <div>
            <!-- اطلاعات اصلی -->
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        اطلاعات دستورکار
                    </h3>
                </div>
                <div class="card-body">
                    <table class="pdm-table">
                        <tbody>
                            <tr>
                                <th style="width: 180px;">عنوان</th>
                                <td><strong><?= pdm_e($wo['title']) ?></strong></td>
                            </tr>
                            <tr>
                                <th>دارایی</th>
                                <td>
                                    <a href="<?= pdm_url('asset', 'show', ['id' => $wo['asset_id']]) ?>"
                                       style="color: var(--pdm-primary-dark);">
                                        <?= pdm_e($wo['asset_name'] ?? '—') ?>
                                    </a>
                                    <?php if (!empty($wo['asset_code'])): ?>
                                        — <code><?= pdm_e($wo['asset_code']) ?></code>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>نوع نگهداری</th>
                                <td><?= pdm_e($wo['maintenance_type_name'] ?? '—') ?></td>
                            </tr>
                            <?php if (!empty($wo['description'])): ?>
                            <tr>
                                <th>توضیحات</th>
                                <td><?= nl2br(pdm_e($wo['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($wo['resolution'])): ?>
                            <tr>
                                <th>اقدام انجام‌شده</th>
                                <td><?= nl2br(pdm_e($wo['resolution'])) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>تاریخ ایجاد</th>
                                <td><?= pdm_date($wo['created_at'], 'Y/m/d H:i') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- تغییر وضعیت -->
            <?php if (in_array($wo['status'], ['open', 'in_progress'])): ?>
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exchange-alt"></i>
                        تغییر وضعیت
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= pdm_url('workorder', 'changeStatus', ['id' => $wo['id']]) ?>">
                        <?= $this->csrfField() ?>
                        
                        <div class="pdm-form-group">
                            <label class="pdm-form-label">وضعیت جدید</label>
                            <select name="status" class="pdm-form-control">
                                <?php if ($wo['status'] === 'open'): ?>
                                    <option value="in_progress">شروع کار (در حال انجام)</option>
                                <?php endif; ?>
                                <?php if ($wo['status'] === 'in_progress'): ?>
                                    <option value="completed">اتمام کار (تکمیل شده)</option>
                                <?php endif; ?>
                                <option value="cancelled">لغو دستورکار</option>
                            </select>
                        </div>

                        <div class="pdm-form-group">
                            <label class="pdm-form-label">اقدام انجام‌شده / توضیحات</label>
                            <textarea name="resolution" class="pdm-form-control" rows="3"
                                      placeholder="شرح کارهای انجام‌شده..."></textarea>
                        </div>

                        <button type="submit" class="btn-pdm-primary">
                            <i class="fas fa-check"></i> اعمال تغییر
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ستون راست -->
        <div>
            <!-- تاریخچه -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        تاریخچه
                    </h3>
                </div>
                <?php if (empty($logs)): ?>
                    <div class="pdm-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-history"></i>
                        <p>تاریخچه‌ای ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($logs as $log): ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb;">
                                <div style="font-size: 0.85rem; color: #374151;">
                                    <?= pdm_e($log['action'] ?? '') ?>
                                </div>
                                <small class="pdm-text-muted">
                                    <?= pdm_date($log['created_at'] ?? null, 'Y/m/d H:i') ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>