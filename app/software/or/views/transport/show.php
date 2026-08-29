<?php
/**
 * نمایش اختصاصی پروژه حمل و نقل
 * مسیر: app/software/or/views/transport/show.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-truck text-primary"></i> <?= or_e($project['name']) ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transport') ?>">حمل و نقل</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=transport&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-edit"></i> ویرایش پروژه
            </a>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <!-- تب‌های اختصاصی حمل و نقل -->
    <ul class="nav nav-tabs mb-4" id="transportTabs">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'nodes' ? 'active' : '' ?>" href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=nodes') ?>">
                <i class="fas fa-map-marker-alt"></i> مبادی و مقاصد
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'matrix' ? 'active' : '' ?>" href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=matrix') ?>">
                <i class="fas fa-table"></i> ماتریس هزینه
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play-circle"></i> حل مسئله
            </a>
        </li>
        <?php if ($project['status'] === 'solved'): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-bar"></i> نتایج و تحلیل
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- محتوای تب‌ها -->
    <div class="tab-content">
        
        <!-- ==================== تب ۱: اطلاعات ==================== -->
        <?php if ($tab === 'info'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">توضیحات پروژه</h5>
                    <p class="text-muted"><?= nl2br(or_e($project['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">وضعیت توازن</small>
                                <strong class="<?= $project['is_balanced'] ? 'text-success' : 'text-warning' ?>">
                                    <?= $project['is_balanced'] ? '✅ متوازن' : '⚠️ نامتوازن' ?>
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">کل عرضه</small>
                                <strong class="text-primary"><?= number_format($project['total_supply'] ?? 0) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">کل تقاضا</small>
                                <strong class="text-info"><?= number_format($project['total_demand'] ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- ==================== تب ۲: مبادی و مقاصد ==================== -->
        <?php elseif ($tab === 'nodes'): ?>
            <div class="row g-4">
                <!-- مبادی (عرضه) -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-primary"><i class="fas fa-warehouse"></i> مبادی (عرضه)</h5>
                            <span class="badge bg-primary">تعداد: <?= count($sources) ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>نام مبدأ</th>
                                            <th class="text-center">ظرفیت عرضه</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sources as $i => $src): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= or_e($src['name']) ?></td>
                                                <td class="text-center fw-bold text-primary"><?= number_format($src['capacity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active fw-bold">
                                            <td colspan="2" class="text-end">جمع کل عرضه:</td>
                                            <td class="text-center text-primary"><?= number_format($balance['supply'] ?? 0) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- مقاصد (تقاضا) -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-info"><i class="fas fa-map-marker-alt"></i> مقاصد (تقاضا)</h5>
                            <span class="badge bg-info text-dark">تعداد: <?= count($destinations) ?></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>نام مقصد</th>
                                            <th class="text-center">میزان تقاضا</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($destinations as $j => $dst): ?>
                                            <tr>
                                                <td><?= $j + 1 ?></td>
                                                <td><?= or_e($dst['name']) ?></td>
                                                <td class="text-center fw-bold text-info"><?= number_format($dst['capacity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active fw-bold">
                                            <td colspan="2" class="text-end">جمع کل تقاضا:</td>
                                            <td class="text-center text-info"><?= number_format($balance['demand'] ?? 0) ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- وضعیت توازن -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body text-center">
                    <h5 class="mb-3">وضعیت توازن مسئله</h5>
                    <?php if ($project['is_balanced']): ?>
                        <div class="alert alert-success d-inline-block mb-0">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <h5 class="mb-0">مسئله متوازن است</h5>
                            <small>عرضه کل (<?= number_format($balance['supply'] ?? 0) ?>) = تقاضای کل (<?= number_format($balance['demand'] ?? 0) ?>)</small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning d-inline-block mb-0">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h5 class="mb-0">مسئله نامتوازن است</h5>
                            <small>برای حل مسئله، ابتدا باید با افزودن گره مجازی (Dummy)، توازن را برقرار کنید.</small>
                        </div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <a href="<?= or_url('controller=transport&action=edit&id=' . $project['id']) ?>" class="btn btn-or-primary">
                            <i class="fas fa-edit"></i> ویرایش گره‌ها و توازن
                        </a>
                    </div>
                </div>
            </div>

        <!-- ==================== تب ۳: ماتریس هزینه ==================== -->
        <?php elseif ($tab === 'matrix'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table text-success"></i> ماتریس هزینه حمل و نقل</h5>
                    <a href="<?= or_url('controller=transport&action=edit&id=' . $project['id']) ?>" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i> ویرایش ماتریس
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($sources) || empty($destinations)): ?>
                        <div class="alert alert-info text-center">
                            هنوز مبادی یا مقاصدی تعریف نشده است. لطفاً ابتدا از تب "مبادی و مقاصد" اقدام به تعریف کنید.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="or-matrix text-center">
                                <thead>
                                    <tr>
                                        <th class="supply-demand-cell" style="min-width: 120px;">مبدأ \ مقصد</th>
                                        <?php foreach ($destinations as $dst): ?>
                                            <th><?= or_e($dst['name']) ?></th>
                                        <?php endforeach; ?>
                                        <th class="supply-demand-cell">عرضه</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // ساخت آرایه کمکی برای دسترسی سریع به هزینه‌ها
                                    $costMap = [];
                                    foreach ($edges as $edge) {
                                        $costMap[$edge['source_id']][$edge['destination_id']] = [
                                            'cost' => $edge['cost'],
                                            'is_prohibited' => $edge['is_prohibited']
                                        ];
                                    }

                                    foreach ($sources as $i => $src): 
                                    ?>
                                        <tr>
                                            <th class="supply-demand-cell"><?= or_e($src['name']) ?></th>
                                            <?php foreach ($destinations as $j => $dst): ?>
                                                <?php 
                                                $cell = $costMap[$src['id']][$dst['id']] ?? ['cost' => null, 'is_prohibited' => 1];
                                                $isProhib = $cell['is_prohibited'];
                                                $cost = $cell['cost'];
                                                $cellClass = $isProhib ? 'prohibited-cell' : '';
                                                $displayCost = $isProhib ? '∞' : ($cost !== null ? number_format($cost, 2) : '-');
                                                ?>
                                                <td class="<?= $cellClass ?>" title="<?= $isProhib ? 'مسیر ممنوعه' : 'هزینه واحد: ' . $cost ?>">
                                                    <?= $displayCost ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="supply-demand-cell fw-bold text-primary"><?= number_format($src['capacity']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <th class="supply-demand-cell">تقاضا</th>
                                        <?php foreach ($destinations as $dst): ?>
                                            <td class="supply-demand-cell fw-bold text-info"><?= number_format($dst['capacity']) ?></td>
                                        <?php endforeach; ?>
                                        <td class="supply-demand-cell">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-muted small">
                            <span class="badge bg-danger me-2">∞</span> نشان‌دهنده مسیر ممنوعه (Prohibited) است.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۴: حل مسئله ==================== -->
        <?php elseif ($tab === 'solve'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <?php if ($project['is_balanced'] == 0): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> قبل از حل، باید مسئله را متوازن کنید. لطفاً به تب "مبادی و مقاصد" بروید و گره مجازی اضافه کنید.
                        </div>
                    <?php else: ?>
                        <h4 class="mb-3">آماده حل مسئله حمل و نقل</h4>
                        <p class="text-muted mb-4">روش حل مورد نظر خود را انتخاب کنید تا الگوریتم اجرا شود.</p>
                        
                        <div class="row justify-content-center">
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-or-primary w-100" onclick="runTransportSolve('NWC')">
                                    <i class="fas fa-angle-double-right"></i> گوشه شمال غربی (NWC)
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-or-primary w-100" onclick="runTransportSolve('LCM')">
                                    <i class="fas fa-coins"></i> کمترین هزینه (LCM)
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-or-success w-100" onclick="runTransportSolve('VAM')">
                                    <i class="fas fa-star"></i> تقریب ووگل (VAM) + MODI
                                </button>
                            </div>
                        </div>
                        
                        <div id="solveLoader" class="d-none mt-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">در حال حل مسئله و بهینه‌سازی...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۵: نتایج و تحلیل ==================== -->
        <?php elseif ($tab === 'result'): ?>
            <?php if ($project['status'] !== 'solved' || empty($solution)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hourglass-half fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">هنوز مسئله حل نشده است</h4>
                        <p class="text-muted mb-4">لطفاً به تب "حل مسئله" بروید و الگوریتم مورد نظر را اجرا کنید.</p>
                        <a href="<?= or_url('controller=transport&action=show&id=' . $project['id'] . '&tab=solve') ?>" class="btn btn-or-primary">
                            <i class="fas fa-play"></i> رفتن به تب حل مسئله
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- کارت‌های خلاصه نتایج -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">هزینه کل بهینه</h6>
                            <h3 class="mb-0 text-success fw-bold"><?= number_format($solution['optimal_cost'] ?? $project['optimal_value'] ?? 0, 2) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">روش حل استفاده‌شده</h6>
                            <h4 class="mb-0 text-primary fw-bold"><?= $solution['method'] ?? 'نامشخص' ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">تعداد تکرار (MODI)</h6>
                            <h4 class="mb-0 text-info fw-bold"><?= $solution['iterations'] ?? 0 ?></h4>
                        </div>
                    </div>
                </div>

                <!-- پیام هوشمند حل‌کننده -->
                <?php if (!empty($solution['smart_feedback'])): ?>
                    <div class="alert alert-<?= ($solution['has_prohibited'] ?? false) ? 'warning' : 'success' ?> mb-4">
                        <i class="fas fa-robot me-2"></i>
                        <?= $solution['smart_feedback'] ?>
                    </div>
                <?php endif; ?>

                <!-- جدول تخصیص نهایی -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="fas fa-check-double text-success"></i> جدول تخصیص نهایی (Basic Cells)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($solution['allocation'])): ?>
                            <div class="table-responsive">
                                <table class="or-matrix text-center">
                                    <thead>
                                        <tr>
                                            <th class="supply-demand-cell">مبدأ \ مقصد</th>
                                            <?php foreach ($destinations as $dst): ?>
                                                <th><?= or_e($dst['name']) ?></th>
                                            <?php endforeach; ?>
                                            <th class="supply-demand-cell">عرضه</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $allocMatrix = $solution['allocation'];
                                        foreach ($sources as $i => $src): 
                                        ?>
                                            <tr>
                                                <th class="supply-demand-cell"><?= or_e($src['name']) ?></th>
                                                <?php foreach ($destinations as $j => $dst): ?>
                                                    <?php 
                                                    $alloc = $allocMatrix[$i][$j] ?? 0;
                                                    $isBasic = $alloc > 0;
                                                    $cellClass = $isBasic ? 'allocation-cell basic-cell' : '';
                                                    ?>
                                                    <td class="<?= $cellClass ?>">
                                                        <?= $isBasic ? number_format($alloc, 2) : '-' ?>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="supply-demand-cell fw-bold text-primary"><?= number_format($src['capacity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <th class="supply-demand-cell">تقاضا</th>
                                            <?php foreach ($destinations as $dst): ?>
                                                <td class="supply-demand-cell fw-bold text-info"><?= number_format($dst['capacity']) ?></td>
                                            <?php endforeach; ?>
                                            <td class="supply-demand-cell">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3 text-muted small">
                                <span class="badge bg-success me-2">سلول‌های پررنگ</span> نشان‌دهنده سلول‌های پایه (Basic Cells) در جواب بهینه هستند.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">اطلاعات تخصیص در دسترس نیست. لطفاً مسئله را مجدداً حل کنید.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- دکمه‌های عملیاتی -->
                <div class="d-flex justify-content-between">
                    <a href="<?= or_url('controller=transport') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> بازگشت به لیست
                    </a>
                    <button class="btn btn-or-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> چاپ گزارش
                    </button>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-secondary">محتوای این تب در حال تکمیل است.</div>
        <?php endif; ?>
    </div>
</div>

<script>
// اجرای الگوریتم حل مسئله
async function runTransportSolve(methodCode) {
    const loader = document.getElementById('solveLoader');
    loader.classList.remove('d-none');
    
    try {
        const res = await fetch('<?= or_url("controller=transport&action=solve&id=" . $project['id']) ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ method_code: methodCode })
        });
        const data = await res.json();
        
        if (data.success) {
            alert('✅ ' + data.message + '\nهزینه بهینه: ' + data.result.optimal_cost);
            window.location.href = '<?= or_url("controller=transport&action=show&id=" . $project['id'] . "&tab=result") ?>';
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        loader.classList.add('d-none');
    }
}

// حذف پروژه
function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= or_url("controller=transport&action=delete&id=") ?>' + id;
    }
}
</script>