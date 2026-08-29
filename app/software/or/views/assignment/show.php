<?php
/**
 * نمایش اختصاصی پروژه تخصیص
 * مسیر: app/software/or/views/assignment/show.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-users-cog text-primary"></i> <?= or_e($project['name']) ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=assignment') ?>">تخصیص</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=assignment&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=assignment&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'nodes' ? 'active' : '' ?>" href="<?= or_url('controller=assignment&action=show&id=' . $project['id'] . '&tab=nodes') ?>">
                <i class="fas fa-users"></i> عوامل و وظایف
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'matrix' ? 'active' : '' ?>" href="<?= or_url('controller=assignment&action=show&id=' . $project['id'] . '&tab=matrix') ?>">
                <i class="fas fa-table"></i> ماتریس هزینه
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" href="<?= or_url('controller=assignment&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play-circle"></i> حل مسئله
            </a>
        </li>
        <?php if ($project['status'] === 'solved'): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=assignment&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-bar"></i> نتایج و تحلیل
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content">
        
        <?php if ($tab === 'info'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5>توضیحات پروژه</h5>
                    <p><?= nl2br(or_e($project['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
                    <hr>
                    <div class="row">
                        <div class="col-md-4"><strong>هدف مسئله:</strong> <?= $project['objective'] === 'minimize' ? 'کمینه‌سازی' : 'بیشینه‌سازی' ?></div>
                        <div class="col-md-4"><strong>تعداد عوامل:</strong> <?= count($sources) ?></div>
                        <div class="col-md-4"><strong>تعداد وظایف:</strong> <?= count($destinations) ?></div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'nodes'): ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-primary"><i class="fas fa-user-tie"></i> عوامل (Agents)</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>#</th><th>نام عامل</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sources as $i => $src): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= or_e($src['name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 text-info"><i class="fas fa-tasks"></i> وظایف (Tasks)</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>#</th><th>نام وظیفه</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($destinations as $j => $dst): ?>
                                        <tr>
                                            <td><?= $j + 1 ?></td>
                                            <td><?= or_e($dst['name']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'matrix'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table text-success"></i> ماتریس هزینه/سود</h5>
                    <a href="<?= or_url('controller=assignment&action=edit&id=' . $project['id']) ?>" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-edit"></i> ویرایش
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($sources) || empty($destinations)): ?>
                        <div class="alert alert-info">هنوز عوامل یا وظایفی تعریف نشده است.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="or-matrix text-center">
                                <thead>
                                    <tr>
                                        <th>عامل \ وظیفه</th>
                                        <?php foreach ($destinations as $dst): ?>
                                            <th><?= or_e($dst['name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $costMap = [];
                                    foreach ($edges as $edge) {
                                        $costMap[$edge['source_id']][$edge['destination_id']] = [
                                            'cost' => $edge['cost'],
                                            'is_prohibited' => $edge['is_prohibited']
                                        ];
                                    }
                                    foreach ($sources as $src): 
                                    ?>
                                        <tr>
                                            <th class="supply-demand-cell"><?= or_e($src['name']) ?></th>
                                            <?php foreach ($destinations as $dst): ?>
                                                <?php 
                                                $cell = $costMap[$src['id']][$dst['id']] ?? ['cost' => null, 'is_prohibited' => 1];
                                                $isProhib = $cell['is_prohibited'];
                                                $cost = $cell['cost'];
                                                $cellClass = $isProhib ? 'prohibited-cell' : '';
                                                $displayCost = $isProhib ? '∞' : ($cost !== null ? number_format($cost, 2) : '-');
                                                ?>
                                                <td class="<?= $cellClass ?>"><?= $displayCost ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($tab === 'solve'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <?php if (count($sources) !== count($destinations)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> تعداد عوامل و وظایف باید برابر باشد.
                        </div>
                    <?php else: ?>
                        <h4 class="mb-3">حل مسئله تخصیص با الگوریتم مجاری (Hungarian)</h4>
                        <p class="text-muted mb-4">هدف: <?= $project['objective'] === 'minimize' ? 'کمینه‌سازی هزینه/زمان' : 'بیشینه‌سازی سود/کارایی' ?></p>
                        <button class="btn btn-or-success btn-lg" onclick="runAssignmentSolve()">
                            <i class="fas fa-play"></i> شروع حل مسئله
                        </button>
                        <div id="solveLoader" class="d-none mt-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">در حال اجرای الگوریتم Hungarian...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($tab === 'result'): ?>
            <?php if ($project['status'] !== 'solved' || empty($solution)): ?>
                <div class="alert alert-warning">هنوز مسئله حل نشده است.</div>
            <?php else: ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">مقدار بهینه</h6>
                            <h3 class="mb-0 text-success fw-bold"><?= number_format($solution['total_cost'] ?? 0, 2) ?></h3>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">روش حل</h6>
                            <h4 class="mb-0 text-primary fw-bold">Hungarian</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="or-stat-card text-center">
                            <h6 class="text-muted mb-2">هدف</h6>
                            <h4 class="mb-0 text-info fw-bold"><?= $project['objective'] === 'minimize' ? 'کمینه' : 'بیشینه' ?></h4>
                        </div>
                    </div>
                </div>

                <?php if (!empty($solution['smart_feedback'])): ?>
                    <div class="alert alert-<?= ($solution['has_prohibited'] ?? false) ? 'warning' : 'success' ?>">
                        <i class="fas fa-robot me-2"></i> <?= $solution['smart_feedback'] ?>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="fas fa-check-double text-success"></i> تخصیص‌های بهینه</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($solution['assignments'])): ?>
                            <table class="or-matrix">
                                <thead>
                                    <tr>
                                        <th>عامل</th>
                                        <th>وظیفه تخصیص‌یافته</th>
                                        <th>هزینه/سود</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($solution['assignments'] as $assign): ?>
                                        <tr>
                                            <td class="fw-bold"><?= or_e($sources[$assign['agent_index']]['name'] ?? 'عامل ' . ($assign['agent_index'] + 1)) ?></td>
                                            <td class="fw-bold"><?= or_e($destinations[$assign['task_index']]['name'] ?? 'وظیفه ' . ($assign['task_index'] + 1)) ?></td>
                                            <td class="text-success fw-bold"><?= number_format($assign['cost'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
async function runAssignmentSolve() {
    const loader = document.getElementById('solveLoader');
    loader.classList.remove('d-none');
    
    try {
        const res = await fetch('<?= or_url("controller=assignment&action=solve&id=" . $project['id']) ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        const data = await res.json();
        
        if (data.success) {
            alert('✅ ' + data.message + '\nمقدار بهینه: ' + data.result.total_cost);
            window.location.href = '<?= or_url("controller=assignment&action=show&id=" . $project['id'] . "&tab=result") ?>';
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        loader.classList.add('d-none');
    }
}

function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟')) {
        window.location.href = '<?= or_url("controller=assignment&action=delete&id=") ?>' + id;
    }
}
</script>