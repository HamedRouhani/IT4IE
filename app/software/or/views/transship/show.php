<?php
/**
 * نمایش اختصاصی پروژه ترانشیپمنت
 * مسیر: app/software/or/views/transship/show.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-project-diagram text-primary"></i> <?= or_e($project['name']) ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transship') ?>">ترانشیپمنت</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=transship&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <!-- تب‌های اختصاصی ترانشیپمنت -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'nodes' ? 'active' : '' ?>" href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=nodes') ?>">
                <i class="fas fa-map-marker-alt"></i> گره‌ها
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'matrix' ? 'active' : '' ?>" href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=matrix') ?>">
                <i class="fas fa-table"></i> ماتریس هزینه
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play-circle"></i> حل مسئله
            </a>
        </li>
        <!-- ✅ اصلاح: تب نتایج همیشه نمایش داده شود -->
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-bar"></i> نتایج و تحلیل
            </a>
        </li>
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
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">تعداد مبادی</small>
                                <strong class="text-primary"><?= count($sources) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">تعداد مقاصد</small>
                                <strong class="text-info"><?= count($destinations) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">تعداد گره‌های میانی</small>
                                <strong class="text-warning"><?= count($intermediates) ?></strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block">وضعیت توازن</small>
                                <strong class="<?= $project['is_balanced'] ? 'text-success' : 'text-warning' ?>">
                                    <?= $project['is_balanced'] ? '✅ متوازن' : '⚠️ نامتوازن' ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- ==================== تب ۲: گره‌ها ==================== -->
        <?php elseif ($tab === 'nodes'): ?>
            <div class="row g-4">
                <!-- مبادی (عرضه) -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0"><i class="fas fa-warehouse"></i> مبادی (<?= count($sources) ?>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($sources as $src): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?= or_e($src['name']) ?></span>
                                        <span class="badge bg-primary rounded-pill"><?= number_format($src['capacity']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- مقاصد (تقاضا) -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white py-3">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> مقاصد (<?= count($destinations) ?>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($destinations as $dst): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?= or_e($dst['name']) ?></span>
                                        <span class="badge bg-info text-dark rounded-pill"><?= number_format($dst['capacity']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- گره‌های میانی (ترانشیپمنت) -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-warning text-dark py-3">
                            <h6 class="mb-0"><i class="fas fa-exchange-alt"></i> گره‌های میانی (<?= count($intermediates) ?>)</h6>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($intermediates as $mid): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?= or_e($mid['name']) ?></span>
                                        <span class="badge bg-warning text-dark rounded-pill"><?= number_format($mid['capacity']) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning d-inline-block mb-0">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                            <h5 class="mb-0">مسئله نامتوازن است</h5>
                            <small>برای حل مسئله، ابتدا باید توازن را برقرار کنید.</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۳: ماتریس هزینه ==================== -->
        <?php elseif ($tab === 'matrix'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table text-success"></i> ماتریس هزینه ترانشیپمنت</h5>
                    <a href="<?= or_url('controller=transship&action=edit&id=' . $project['id']) ?>" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i> ویرایش ماتریس
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($sources) && empty($destinations) && empty($intermediates)): ?>
                        <div class="alert alert-info text-center">
                            هنوز گره‌ای تعریف نشده است. لطفاً ابتدا از تب "گره‌ها" اقدام به تعریف کنید.
                        </div>
                    <?php else: ?>
                        <?php
                        $allNodes = array_merge($sources, $destinations, $intermediates);
                        $costMap = [];
                        foreach ($edges as $edge) {
                            $costMap[$edge['source_id']][$edge['destination_id']] = [
                                'cost' => $edge['cost'],
                                'is_prohibited' => $edge['is_prohibited']
                            ];
                        }
                        ?>
                        <div class="table-responsive">
                            <table class="or-matrix text-center">
                                <thead>
                                    <tr>
                                        <th class="supply-demand-cell" style="min-width: 120px;">گره \ گره</th>
                                        <?php foreach ($allNodes as $node): ?>
                                            <th><?= or_e($node['name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allNodes as $src): ?>
                                        <tr>
                                            <th class="supply-demand-cell"><?= or_e($src['name']) ?></th>
                                            <?php foreach ($allNodes as $dst): ?>
                                                <?php 
                                                if ($src['id'] === $dst['id']): ?>
                                                    <td class="text-muted">-</td>
                                                <?php else:
                                                    $cell = $costMap[$src['id']][$dst['id']] ?? null;
                                                    $isProhib = $cell ? $cell['is_prohibited'] : 1;
                                                    $cost = $cell ? $cell['cost'] : null;
                                                    $cellClass = $isProhib ? 'prohibited-cell' : '';
                                                    $displayCost = $isProhib ? '∞' : ($cost !== null ? number_format($cost, 2) : '-');
                                                ?>
                                                    <td class="<?= $cellClass ?>" title="<?= $isProhib ? 'مسیر ممنوعه' : 'هزینه واحد: ' . $cost ?>">
                                                        <?= $displayCost ?>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
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
                    <?php if (empty($sources) && empty($destinations) && empty($intermediates)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> ابتدا گره‌ها و یال‌ها را تعریف کنید.
                        </div>
                    <?php else: ?>
                        <h4 class="mb-3">حل مسئله ترانشیپمنت</h4>
                        <p class="text-muted mb-4">
                            مسئله ترانشیپمنت با روش تبدیل به حمل و نقل و الگوریتم VAM+MODI حل می‌شود.
                        </p>
                        
                        <button class="btn btn-or-success btn-lg" onclick="runTransshipSolve()">
                            <i class="fas fa-play"></i> شروع حل مسئله
                        </button>
                        
                        <div id="solveLoader" class="d-none mt-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">در حال حل مسئله...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۵: نتایج و تحلیل ==================== -->
        <?php elseif ($tab === 'result'): ?>
            <?php 
            $solution = json_decode($project['solution_data'] ?? 'null', true);
            ?>
            
            <?php if ($project['status'] !== 'solved' || empty($solution)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hourglass-half fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">هنوز مسئله حل نشده است</h4>
                        <p class="text-muted mb-4">
                            وضعیت فعلی: <strong><?= or_getStatusLabel($project['status']) ?></strong><br>
                            لطفاً به تب "حل مسئله" بروید و الگوریتم را اجرا کنید.
                        </p>
                        <a href="<?= or_url('controller=transship&action=show&id=' . $project['id'] . '&tab=solve') ?>" class="btn btn-or-primary">
                            <i class="fas fa-play"></i> رفتن به تب حل مسئله
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- نمایش نتایج وقتی پروژه حل شده -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">هزینه کل بهینه</h6>
                            <h3 class="mb-0 text-success fw-bold"><?= number_format($solution['total_cost'] ?? 0, 2) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">روش حل</h6>
                            <h5 class="mb-0 text-primary fw-bold"><?= $solution['method'] ?? 'VAM + MODI' ?></h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">تعداد تکرار</h6>
                            <h4 class="mb-0 text-info fw-bold"><?= $solution['iterations'] ?? 0 ?></h4>
                        </div>
                    </div>
                </div>

                <?php if (!empty($solution['smart_feedback'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-robot me-2"></i> <?= $solution['smart_feedback'] ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="fas fa-route text-success"></i> مسیرهای تخصیص‌یافته</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($solution['allocations'])): ?>
                            <div class="table-responsive">
                                <table class="or-matrix">
                                    <thead>
                                        <tr>
                                            <th>از گره</th>
                                            <th>به گره</th>
                                            <th>مقدار</th>
                                            <th>هزینه واحد</th>
                                            <th>هزینه کل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($solution['allocations'] as $alloc): ?>
                                            <tr>
                                                <td class="fw-bold"><?= or_e($alloc['from']) ?></td>
                                                <td class="fw-bold"><?= or_e($alloc['to']) ?></td>
                                                <td class="text-primary fw-bold"><?= number_format($alloc['amount'], 2) ?></td>
                                                <td><?= number_format($alloc['unit_cost'], 2) ?></td>
                                                <td class="text-success fw-bold"><?= number_format($alloc['total_cost'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">هیچ تخصیصی در جواب بهینه یافت نشد.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= or_url('controller=transship') ?>" class="btn btn-outline-secondary">
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
async function runTransshipSolve() {
    const loader = document.getElementById('solveLoader');
    loader.classList.remove('d-none');
    
    try {
        const res = await fetch('<?= or_url("controller=transship&action=solve&id=" . $project['id']) ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const data = await res.json();
        
        if (data.success) {
            // تأخیر کوتاه برای اطمینان از بسته شدن loader
            setTimeout(function() {
                alert('✅ ' + data.message + '\nهزینه بهینه: ' + data.result.total_cost);
                // هدایت به تب نتایج
                window.location.href = '<?= or_url("controller=transship&action=show&id=" . $project['id'] . "&tab=result") ?>';
            }, 100);
        } else {
            alert('❌ خطا: ' + data.error);
            loader.classList.add('d-none');
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
        loader.classList.add('d-none');
    }
}

// حذف پروژه
function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= or_url("controller=transship&action=delete&id=") ?>' + id;
    }
}
</script>