<?php
$pageTitle = ($project['name'] ?? 'پروژه') . ' - PMBOK';
$activePage = 'project';
$progress = pmbok_getProgressPercentage($project['task_count'] ?? 0, $project['task_count'] ?? 1);
?>

<div class="page-header">
    <div>
        <nav class="breadcrumb">
            <a href="?controller=project">پروژه‌ها</a> /
            <span><?= htmlspecialchars($project['name']) ?></span>
        </nav>
        <h2><i class="fas fa-project-diagram"></i> <?= htmlspecialchars($project['name']) ?></h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="?controller=project&action=edit&id=<?= $project['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> ویرایش
        </a>
    </div>
</div>

<!-- اطلاعات پروژه -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پروژه</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">فاز:</span>
            <span class="badge badge-<?= pmbok_getPhaseColor($project['phase']) ?>">
                <?= pmbok_getPhaseLabel($project['phase']) ?>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">متدولوژی:</span>
            <span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">تاریخ ایجاد:</span>
            <span><?= pmbok_showDate($project['created_at']) ?></span>
        </div>
    </div>
    <div style="margin-top: 15px;">
        <strong>توضیحات:</strong>
        <p class="text-muted" style="margin-top: 5px;">
            <?= htmlspecialchars($project['description'] ?? 'توضیحاتی ثبت نشده است.') ?>
        </p>
    </div>
</div>

<!-- تب‌های جزئیات -->
<div class="tabs-container">
    <div class="tabs">
        <button class="tab-btn active" onclick="showTab(event, 'deliverables')">
            <i class="fas fa-box"></i> تحویل‌دادنی‌ها (<?= count($deliverables ?? []) ?>)
        </button>
        <button class="tab-btn" onclick="showTab(event, 'risks')">
            <i class="fas fa-exclamation-triangle"></i> ریسک‌ها (<?= count($risks ?? []) ?>)
        </button>
        <button class="tab-btn" onclick="showTab(event, 'stakeholders')">
            <i class="fas fa-users"></i> ذی‌نفعان (<?= count($stakeholders ?? []) ?>)
        </button>
        <button class="tab-btn" onclick="showTab(event, 'tasks')">
            <i class="fas fa-tasks"></i> فرآیندها (<?= count($projectTasks ?? []) ?>)
        </button>
    </div>
    
    <!-- تب تحویل‌دادنی‌ها -->
    <div class="tab-content active" id="deliverables">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="card-title" style="margin: 0;"><i class="fas fa-box"></i> تحویل‌دادنی‌ها</h3>
            </div>
            
            <form method="POST" action="?controller=project&action=addDeliverable&id=<?= $project['id'] ?>" class="inline-form">
                <div class="form-row">
                    <input type="text" name="deliverable_name" class="form-control" placeholder="نام تحویل‌دادنی" required>
                    <input type="text" name="deliverable_description" class="form-control" placeholder="توضیحات">
                    <select name="deliverable_status" class="form-select">
                        <option value="pending">در انتظار</option>
                        <option value="in_progress">در حال انجام</option>
                        <option value="completed">تکمیل شده</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> افزودن
                    </button>
                </div>
            </form>
            
            <?php if (empty($deliverables)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ تحویل‌دادنی ثبت نشده است.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>توضیحات</th>
                            <th>وضعیت</th>
                            <th>تاریخ برنامه‌ریزی</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deliverables as $d): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                            <td><?= htmlspecialchars(pmbok_truncateText($d['description'], 50)) ?></td>
                            <td>
                                <?php 
                                $statusBadge = ['pending'=>'badge-secondary', 'in_progress'=>'badge-warning', 'completed'=>'badge-success', 'approved'=>'badge-primary'];
                                ?>
                                <span class="badge <?= $statusBadge[$d['status']] ?? 'badge-secondary' ?>">
                                    <?= $d['status'] ?>
                                </span>
                            </td>
                            <td><?= pmbok_showDate($d['planned_date']) ?></td>
                            <td>
                                <form method="POST" action="?controller=project&action=deleteDeliverable&id=<?= $project['id'] ?>" 
                                      style="display: inline;" onsubmit="return confirm('حذف شود؟')">
                                    <input type="hidden" name="deliverable_id" value="<?= $d['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب ریسک‌ها -->
    <div class="tab-content" id="risks">
        <div class="card">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> ریسک‌های پروژه</h3>
            
            <form method="POST" action="?controller=project&action=addRisk&id=<?= $project['id'] ?>" class="inline-form">
                <div class="form-row">
                    <input type="text" name="risk_title" class="form-control" placeholder="عنوان ریسک" required>
                    <select name="risk_probability" class="form-select">
                        <option value="very_low">احتمال بسیار کم</option>
                        <option value="low">احتمال کم</option>
                        <option value="medium" selected>احتمال متوسط</option>
                        <option value="high">احتمال بالا</option>
                        <option value="very_high">احتمال بسیار بالا</option>
                    </select>
                    <select name="risk_impact" class="form-select">
                        <option value="very_low">تاثیر بسیار کم</option>
                        <option value="low">تاثیر کم</option>
                        <option value="medium" selected>تاثیر متوسط</option>
                        <option value="high">تاثیر بالا</option>
                        <option value="very_high">تاثیر بسیار بالا</option>
                    </select>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-plus"></i> افزودن ریسک
                    </button>
                </div>
            </form>
            
            <?php if (empty($risks)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ ریسکی ثبت نشده است.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>عنوان</th>
                            <th>احتمال</th>
                            <th>تاثیر</th>
                            <th>امتیاز</th>
                            <th>وضعیت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($risks as $r): ?>
                        <tr>
                            <td>
                                <a href="?controller=risk&action=show&id=<?= $r['id'] ?>">
                                    <?= htmlspecialchars($r['title']) ?>
                                </a>
                            </td>
                            <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                            <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                            <td><strong style="color: <?= $r['risk_score'] >= 15 ? '#EF4444' : ($r['risk_score'] >= 8 ? '#F59E0B' : '#10B981') ?>"><?= $r['risk_score'] ?></strong></td>
                            <td><span class="badge badge-info"><?= pmbok_getRiskStatusLabel($r['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب ذی‌نفعان -->
    <div class="tab-content" id="stakeholders">
        <div class="card">
            <h3 class="card-title"><i class="fas fa-users"></i> ذی‌نفعان پروژه</h3>
            
            <form method="POST" action="?controller=project&action=addStakeholder&id=<?= $project['id'] ?>" class="inline-form">
                <div class="form-row">
                    <input type="text" name="stakeholder_name" class="form-control" placeholder="نام" required>
                    <input type="text" name="stakeholder_role" class="form-control" placeholder="نقش" required>
                    <input type="email" name="stakeholder_email" class="form-control" placeholder="ایمیل">
                    <select name="stakeholder_influence" class="form-select">
                        <option value="low">نفوذ کم</option>
                        <option value="medium" selected>نفوذ متوسط</option>
                        <option value="high">نفوذ بالا</option>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> افزودن
                    </button>
                </div>
            </form>
            
            <?php if (empty($stakeholders)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ ذی‌نفعی ثبت نشده است.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>نقش</th>
                            <th>ایمیل</th>
                            <th>نفوذ</th>
                            <th>علاقه</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stakeholders as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><?= htmlspecialchars($s['role']) ?></td>
                            <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                            <td><span class="badge"><?= $s['influence'] ?></span></td>
                            <td><span class="badge"><?= $s['interest'] ?></span></td>
                            <td>
                                <form method="POST" action="?controller=project&action=deleteStakeholder&id=<?= $project['id'] ?>" 
                                      style="display: inline;" onsubmit="return confirm('حذف شود؟')">
                                    <input type="hidden" name="stakeholder_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب فرآیندها -->
    <div class="tab-content" id="tasks">
        <div class="card">
            <h3 class="card-title"><i class="fas fa-tasks"></i> فرآیندهای پروژه</h3>
            
            <form method="POST" action="?controller=project&action=addTask&id=<?= $project['id'] ?>" class="inline-form">
                <div class="form-row">
                    <select name="task_id" class="form-select" required>
                        <option value="">انتخاب فرآیند...</option>
                        <?php foreach ($allTasks as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['code']) ?> - <?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="task_status" class="form-select">
                        <option value="not_started">شروع نشده</option>
                        <option value="in_progress">در حال انجام</option>
                        <option value="completed">تکمیل شده</option>
                    </select>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> افزودن فرآیند
                    </button>
                </div>
            </form>
            
            <?php if (empty($projectTasks)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ فرآیندی اضافه نشده است.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام فرآیند</th>
                            <th>حوزه دانشی</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectTasks as $pt): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($pt['task_code']) ?></code></td>
                            <td>
                                <a href="?controller=task&action=show&id=<?= $pt['task_id'] ?>">
                                    <?= htmlspecialchars($pt['task_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($pt['ka_name']) ?></td>
                            <td>
                                <?php 
                                $stBadge = ['not_started'=>'badge-secondary', 'in_progress'=>'badge-warning', 'completed'=>'badge-success', 'deferred'=>'badge-danger'];
                                ?>
                                <span class="badge <?= $stBadge[$pt['status']] ?? 'badge-secondary' ?>">
                                    <?= pmbok_getTaskStatusLabel($pt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="?controller=project&action=deleteTask&id=<?= $project['id'] ?>" 
                                      style="display: inline;" onsubmit="return confirm('حذف شود؟')">
                                    <input type="hidden" name="pt_id" value="<?= $pt['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(event, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    event.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
}
</script>