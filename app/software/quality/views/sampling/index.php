<?php
use App\Software\Quality\Models\SamplingPlan;

$plans  = $plans  ?? [];
$system = $system ?? [];
?>
<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex-between qc-mb-3">
        <h2>
            <i class="fas fa-vials"></i> طرح‌های نمونه‌گیری (Acceptance Sampling)
            <small class="qc-text-muted" style="font-size:.75rem;font-weight:500;">
                — <?= htmlspecialchars($system['company_name'] ?? '') ?>
            </small>
        </h2>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=create" class="btn-qc-primary">
            <i class="fas fa-plus"></i> طرح جدید
        </a>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="qc-alert success qc-mb-3">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <div class="qc-card">
        <div class="qc-card-body" style="padding:0;">
            <?php if (empty($plans)): ?>
                <div class="qc-empty-state">
                    <i class="fas fa-vials"></i>
                    <h4>هنوز طرح نمونه‌گیری ثبت نشده</h4>
                    <p>برای شروع، اولین طرح Acceptance Sampling خود را بسازید</p>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=create" class="btn-qc-primary">
                        <i class="fas fa-plus"></i> ایجاد اولین طرح
                    </a>
                </div>
            <?php else: ?>
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام طرح</th>
                            <th>پروژه</th>
                            <th>نوع</th>
                            <th>AQL</th>
                            <th>LTPD</th>
                            <th>n</th>
                            <th>c</th>
                            <th>α واقعی</th>
                            <th>β واقعی</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $i => $p): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=show&id=<?= (int)$p['id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($p['project_name'] ?? '—') ?></td>
                                <td><code><?= htmlspecialchars($p['plan_type']) ?></code></td>
                                <td><?= $p['aql']  !== null ? number_format((float)$p['aql'], 2) : '—' ?></td>
                                <td><?= $p['ltpd'] !== null ? number_format((float)$p['ltpd'], 2) : '—' ?></td>
                                <td><strong><?= (int)($p['sample_size'] ?? 0) ?></strong></td>
                                <td><strong><?= (int)($p['accept_number'] ?? 0) ?></strong></td>
                                <td><?= $p['producer_risk'] !== null ? number_format((float)$p['producer_risk'], 4) : '—' ?></td>
                                <td><?= $p['consumer_risk'] !== null ? number_format((float)$p['consumer_risk'], 4) : '—' ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=show&id=<?= (int)$p['id'] ?>"
                                       class="btn-qc-outline btn-sm" title="نمایش">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=sampling&action=delete&id=<?= (int)$p['id'] ?>"
                                       class="btn-qc-danger btn-sm qc-confirm-delete"
                                       data-message="آیا از حذف طرح «<?= htmlspecialchars($p['name']) ?>» اطمینان دارید؟"
                                       title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/quality.js"></script>