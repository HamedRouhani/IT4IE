<?php
$pageTitle = $pageTitle ?? 'گزارش پروژه';
$currentPage = $currentPage ?? 'report';
?>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: white !important; font-size: 11pt; }
        .card { border: none !important; box-shadow: none !important; page-break-inside: avoid; }
        .table th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .page-header { display: none !important; }
    }
    .report-section { margin-bottom: 25px; }
    .report-section h4 { 
        border-bottom: 2px solid var(--soft-primary); 
        padding-bottom: 8px; 
        color: var(--soft-primary);
        margin-bottom: 15px;
    }
    .kpi-box {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        border-right: 4px solid var(--soft-primary);
    }
    .kpi-value { font-size: 2rem; font-weight: 700; color: var(--soft-primary); }
    .kpi-label { font-size: 0.85rem; color: var(--gray); margin-top: 5px; }
</style>

<div class="no-print" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
    <a href="?controller=report&action=projects" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت به لیست
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="fas fa-print"></i> چاپ / ذخیره PDF
    </button>
    <a href="?controller=report&action=exportPrimavera&id=<?= $project['id'] ?>" class="btn btn-success">
        <i class="fas fa-file-csv"></i> خروجی Primavera P6
    </a>
    <a href="?controller=report&action=exportToMSProject&id=<?= $project['id'] ?>" class="btn btn-warning">
        <i class="fas fa-file-code"></i> خروجی MSP
    </a>
</div>

<!-- هدر گزارش -->
<div class="card" style="background: linear-gradient(135deg, var(--soft-primary) 0%, #7c3aed 100%); color: white; border: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 style="margin: 0 0 5px 0; color: white;">
                <i class="fas fa-project-diagram"></i> گزارش جامع پروژه
            </h2>
            <h3 style="margin: 0; color: rgba(255,255,255,0.95);"><?= htmlspecialchars($project['name']) ?></h3>
        </div>
        <div style="text-align: left; font-size: 0.85rem; opacity: 0.9;">
            <div>تاریخ تولید: <?= date('Y/m/d H:i') ?></div>
            <div>تهیه‌کننده: <?= htmlspecialchars($_SESSION['user_name'] ?? 'کاربر سیستم') ?></div>
        </div>
    </div>
</div>

<!-- اطلاعات کلی پروژه -->
<div class="card report-section">
    <h4><i class="fas fa-info-circle"></i> اطلاعات کلی پروژه</h4>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <div>
            <strong>فاز:</strong>
            <span class="badge badge-<?= pmbok_getPhaseColor($project['phase']) ?>">
                <?= pmbok_getPhaseLabel($project['phase']) ?>
            </span>
        </div>
        <div>
            <strong>متدولوژی:</strong>
            <span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology']) ?></span>
        </div>
        <div><strong>تاریخ ایجاد:</strong> <?= pmbok_showDate($project['created_at']) ?></div>
        <div><strong>ذی‌نفعان:</strong> <?= $project['stakeholder_count'] ?? 0 ?> نفر</div>
    </div>
    <?php if (!empty($project['description'])): ?>
        <div style="margin-top: 15px;">
            <strong>توضیحات:</strong>
            <p style="margin-top: 5px; color: var(--gray);"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- شاخص‌های کلیدی -->
<div class="report-section">
    <h4 style="border-bottom: 2px solid var(--soft-primary); padding-bottom: 8px; color: var(--soft-primary);">
        <i class="fas fa-chart-line"></i> شاخص‌های کلیدی عملکرد
    </h4>
    <?php 
    // مقادیر پیش‌فرض برای جلوگیری از خطا
    $totalTasks = $progress['total'] ?? 0;
    $completedTasks = $progress['completed'] ?? 0;
    $inProgressTasks = $progress['in_progress'] ?? 0;
    $progressPercent = $progress['percent'] ?? 0;
    $totalRisks = is_array($risks) ? count($risks) : 0;
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <div class="kpi-box">
            <div class="kpi-value"><?= $totalTasks ?></div>
            <div class="kpi-label">کل فرآیندها</div>
        </div>
        <div class="kpi-box" style="border-right-color: #10B981;">
            <div class="kpi-value" style="color: #10B981;"><?= $completedTasks ?></div>
            <div class="kpi-label">تکمیل شده</div>
        </div>
        <div class="kpi-box" style="border-right-color: #F59E0B;">
            <div class="kpi-value" style="color: #F59E0B;"><?= $inProgressTasks ?></div>
            <div class="kpi-label">در حال انجام</div>
        </div>
        <div class="kpi-box" style="border-right-color: #EF4444;">
            <div class="kpi-value" style="color: #EF4444;"><?= $progressPercent ?>%</div>
            <div class="kpi-label">درصد پیشرفت</div>
        </div>
        <div class="kpi-box" style="border-right-color: #8B5CF6;">
            <div class="kpi-value" style="color: #8B5CF6;"><?= $totalRisks ?></div>
            <div class="kpi-label">ریسک‌های ثبت‌شده</div>
        </div>
    </div>
</div>

<!-- فرآیندهای پروژه -->
<div class="card report-section">
    <h4><i class="fas fa-tasks"></i> فرآیندهای پروژه (<?= count($tasks) ?> مورد)</h4>
    <?php if (empty($tasks)): ?>
        <p class="text-muted">هیچ فرآیندی ثبت نشده است.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>نام فرآیند</th>
                        <th>حوزه دانشی</th>
                        <th>وضعیت</th>
                        <th>یادداشت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $t): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($t['code']) ?></code></td>
                        <td><?= htmlspecialchars($t['task_name']) ?></td>
                        <td>
                            <small><?= htmlspecialchars($t['ka_code']) ?> - <?= htmlspecialchars($t['ka_name']) ?></small>
                        </td>
                        <td>
                            <?php 
                            $stBadge = [
                                'not_started' => 'badge-secondary', 
                                'in_progress' => 'badge-warning', 
                                'completed' => 'badge-success', 
                                'deferred' => 'badge-danger'
                            ];
                            ?>
                            <span class="badge <?= $stBadge[$t['status']] ?? 'badge-secondary' ?>">
                                <?= pmbok_getTaskStatusLabel($t['status']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars(pmbok_truncateText($t['notes'] ?? '-', 40)) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ریسک‌ها -->
<?php if (!empty($risks)): ?>
<div class="card report-section">
    <h4><i class="fas fa-exclamation-triangle"></i> ریسک‌های پروژه (<?= count($risks) ?> مورد)</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>عنوان ریسک</th>
                    <th>احتمال</th>
                    <th>تأثیر</th>
                    <th>امتیاز</th>
                    <th>وضعیت</th>
                    <th>استراتژی پاسخ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($risks as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['title']) ?></td>
                    <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                    <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                    <td>
                        <strong style="color: <?= $r['risk_score'] >= 15 ? '#DC2626' : ($r['risk_score'] >= 8 ? '#F59E0B' : '#10B981') ?>">
                            <?= $r['risk_score'] ?>
                        </strong>
                    </td>
                    <td><span class="badge badge-info"><?= pmbok_getRiskStatusLabel($r['status']) ?></span></td>
                    <td><?= htmlspecialchars($r['response_strategy'] ?? '-') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- تحویل‌دادنی‌ها -->
<?php if (!empty($deliverables)): ?>
<div class="card report-section">
    <h4><i class="fas fa-box-open"></i> تحویل‌دادنی‌ها (<?= count($deliverables) ?> مورد)</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>توضیحات</th>
                    <th>وضعیت</th>
                    <th>تاریخ برنامه‌ریزی</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($deliverables as $d): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                    <td><?= htmlspecialchars(pmbok_truncateText($d['description'] ?? '', 50)) ?></td>
                    <td><span class="badge badge-<?= $d['status'] === 'completed' ? 'success' : ($d['status'] === 'in_progress' ? 'warning' : 'secondary') ?>"><?= $d['status'] ?></span></td>
                    <td><?= pmbok_showDate($d['planned_date'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ذی‌نفعان -->
<?php if (!empty($stakeholders)): ?>
<div class="card report-section">
    <h4><i class="fas fa-users"></i> ذی‌نفعان کلیدی (<?= count($stakeholders) ?> نفر)</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>نام</th>
                    <th>نقش</th>
                    <th>نفوذ</th>
                    <th>علاقه‌مندی</th>
                    <th>وضعیت تعامل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stakeholders as $s): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                    <td><?= htmlspecialchars($s['role']) ?></td>
                    <td><span class="badge"><?= $s['influence'] ?></span></td>
                    <td><span class="badge"><?= $s['interest'] ?></span></td>
                    <td><span class="badge badge-info"><?= $s['engagement_status'] ?? 'neutral' ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- پاورقی -->
<div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px; color: var(--gray); font-size: 0.85rem; border-top: 1px solid var(--border);">
    این گزارش به صورت خودکار توسط <strong>PMBOK Analyzer</strong> تولید شده است.
    <br>تاریخ تولید: <?= date('Y/m/d H:i:s') ?>
</div>

<div class="no-print" style="text-align: center; margin-top: 20px;">
    <button onclick="window.print()" class="btn btn-primary btn-lg">
        <i class="fas fa-print"></i> چاپ گزارش
    </button>
</div>