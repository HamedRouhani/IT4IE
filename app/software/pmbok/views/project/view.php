<?php
$pageTitle = ($project['name'] ?? 'پروژه') . ' - PMBOK';
$currentPage = $currentPage ?? 'project';
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
            <i class="fas fa-edit"></i> ویرایش پروژه
        </a>
        <a href="?controller=report&action=exportMsProject&id=<?= $project['id'] ?>" class="btn btn-sm btn-success">
            <i class="fas fa-file-code"></i> خروجی MSP
        </a>
        <a href="?controller=report&action=exportPrimavera&id=<?= $project['id'] ?>" class="btn btn-sm btn-info">
            <i class="fas fa-file-csv"></i> خروجی P6
        </a>
    </div>
</div>

<!-- اطلاعات پروژه -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پروژه</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">فاز:</span>
            <span class="badge badge-<?= pmbok_getPhaseColor($project['phase']) ?>"><?= pmbok_getPhaseLabel($project['phase']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">متدولوژی:</span>
            <span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">صنعت:</span>
            <span class="badge"><?= htmlspecialchars($project['industry'] ?? 'عمومی') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">تاریخ ایجاد:</span>
            <span><?= pmbok_showDate($project['created_at']) ?></span>
        </div>
    </div>
    <?php if (!empty($project['description'])): ?>
    <div style="margin-top: 15px;">
        <strong>توضیحات:</strong>
        <p class="text-muted" style="margin-top: 5px;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- تب‌های جزئیات -->
<div class="tabs-container">
    <div class="tabs">
        <button class="tab-btn" data-tab="deliverables" onclick="switchTab(event, 'deliverables')"><i class="fas fa-box"></i> تحویل‌دادنی‌ها (<?= count($deliverables ?? []) ?>)</button>
        <button class="tab-btn" data-tab="risks" onclick="switchTab(event, 'risks')"><i class="fas fa-exclamation-triangle"></i> ریسک‌ها (<?= count($risks ?? []) ?>)</button>
        <button class="tab-btn" data-tab="stakeholders" onclick="switchTab(event, 'stakeholders')"><i class="fas fa-users"></i> ذی‌نفعان (<?= count($stakeholders ?? []) ?>)</button>
        <button class="tab-btn" data-tab="tasks" onclick="switchTab(event, 'tasks')"><i class="fas fa-tasks"></i> فرآیندها (<?= count($projectTasks ?? []) ?>)</button>
    </div>
    
    <!-- تب ۱: تحویل‌دادنی‌ها -->
    <div class="tab-content" id="deliverables">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="card-title" style="margin: 0;"><i class="fas fa-box"></i> تحویل‌دادنی‌ها</h3>
                <button class="btn btn-primary" onclick="openAddModal('deliverable')">
                    <i class="fas fa-plus"></i> افزودن تحویل‌دادنی
                </button>
            </div>
            
            <?php if (empty($deliverables)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ تحویل‌دادنی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>توضیحات</th>
                                <th>وضعیت</th>
                                <th>تاریخ برنامه‌ریزی</th>
                                <th style="width: 120px; text-align: center;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($deliverables as $d): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                                <td><?= htmlspecialchars(pmbok_truncateText($d['description'] ?? '', 50)) ?></td>
                                <td>
                                    <?php 
                                    $statusColors = ['pending' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success'];
                                    $statusLabels = ['pending' => 'در انتظار', 'in_progress' => 'در حال انجام', 'completed' => 'تکمیل شده'];
                                    ?>
                                    <span class="badge badge-<?= $statusColors[$d['status']] ?? 'secondary' ?>">
                                        <?= $statusLabels[$d['status']] ?? $d['status'] ?>
                                    </span>
                                </td>
                                <td><?= pmbok_showDate($d['planned_date'] ?? '') ?></td>
                                <td style="text-align: center;">
                                    <button class="btn btn-sm btn-info" onclick='openEditModal("deliverable", <?= json_encode($d, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="?controller=project&action=deleteDeliverable&id=<?= $project['id'] ?>" 
                                          style="display:inline;" onsubmit="return confirm('آیا از حذف این تحویل‌دادنی اطمینان دارید؟')">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب ۲: ریسک‌ها -->
    <div class="tab-content" id="risks">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="card-title" style="margin: 0;"><i class="fas fa-exclamation-triangle"></i> ریسک‌ها</h3>
                <button class="btn btn-danger" onclick="openAddModal('risk')">
                    <i class="fas fa-plus"></i> افزودن ریسک
                </button>
            </div>
            
            <?php if (empty($risks)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ ریسکی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>احتمال</th>
                                <th>تأثیر</th>
                                <th>امتیاز</th>
                                <th>وضعیت</th>
                                <th style="width: 120px; text-align: center;">عملیات</th>
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
                                <td style="text-align: center;">
                                    <button class="btn btn-sm btn-info" onclick='openEditModal("risk", <?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="?controller=project&action=deleteRisk&id=<?= $project['id'] ?>" 
                                          style="display:inline;" onsubmit="return confirm('آیا از حذف این ریسک اطمینان دارید؟')">
                                        <input type="hidden" name="risk_id" value="<?= $r['id'] ?>">
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب ۳: ذی‌نفعان -->
    <div class="tab-content" id="stakeholders">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="card-title" style="margin: 0;"><i class="fas fa-users"></i> ذی‌نفعان</h3>
                <button class="btn btn-primary" onclick="openAddModal('stakeholder')">
                    <i class="fas fa-user-plus"></i> افزودن ذی‌نفع
                </button>
            </div>
            
            <?php if (empty($stakeholders)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ ذی‌نفعی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>نام</th>
                                <th>نقش</th>
                                <th>ایمیل</th>
                                <th>نفوذ</th>
                                <th>علاقه</th>
                                <th>وضعیت تعامل</th>
                                <th style="width: 120px; text-align: center;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stakeholders as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                                <td><?= htmlspecialchars($s['role']) ?></td>
                                <td><?= htmlspecialchars($s['email'] ?? '-') ?></td>
                                <td><span class="badge"><?= htmlspecialchars($s['influence']) ?></span></td>
                                <td><span class="badge"><?= htmlspecialchars($s['interest']) ?></span></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($s['engagement_status'] ?? 'neutral') ?></span></td>
                                <td style="text-align: center;">
                                    <button class="btn btn-sm btn-info" onclick='openEditModal("stakeholder", <?= json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="?controller=project&action=deleteStakeholder&id=<?= $project['id'] ?>" 
                                          style="display:inline;" onsubmit="return confirm('آیا از حذف این ذی‌نفع اطمینان دارید؟')">
                                        <input type="hidden" name="stakeholder_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- تب ۴: فرآیندها -->
    <div class="tab-content" id="tasks">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 class="card-title" style="margin: 0;"><i class="fas fa-tasks"></i> فرآیندها</h3>
                <button class="btn btn-success" onclick="openAddModal('task')">
                    <i class="fas fa-plus"></i> افزودن فرآیند
                </button>
            </div>
            
            <?php if (empty($projectTasks)): ?>
                <p class="text-muted text-center" style="padding: 20px;">هیچ فرآیندی اضافه نشده است.</p>
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
                                <th style="width: 120px; text-align: center;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projectTasks as $pt): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($pt['task_code']) ?></code></td>
                                <td><?= htmlspecialchars($pt['task_name']) ?></td>
                                <td><?= htmlspecialchars($pt['ka_name']) ?></td>
                                <td>
                                    <?php 
                                    $taskStatusColors = ['not_started' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success', 'deferred' => 'danger'];
                                    ?>
                                    <span class="badge badge-<?= $taskStatusColors[$pt['status']] ?? 'secondary' ?>">
                                        <?= pmbok_getTaskStatusLabel($pt['status']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(pmbok_truncateText($pt['notes'] ?? '-', 40)) ?></td>
                                <td style="text-align: center;">
                                    <button class="btn btn-sm btn-info" onclick='openEditModal("task", <?= json_encode($pt, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" action="?controller=project&action=deleteTask&id=<?= $project['id'] ?>" 
                                          style="display:inline;" onsubmit="return confirm('آیا از حذف این فرآیند اطمینان دارید؟')">
                                        <input type="hidden" name="pt_id" value="<?= $pt['id'] ?>">
                                        <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal عمومی -->
<div id="itemModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fefefe; margin: 5% auto; padding: 0; border-radius: 12px; width: 90%; max-width: 650px; box-shadow: 0 8px 32px rgba(0,0,0,0.3); animation: modalSlideIn 0.3s;">
        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #6C3CE1 0%, #8B5CF6 100%); color: white; border-radius: 12px 12px 0 0;">
            <h3 id="modalTitle" style="margin: 0;">افزودن</h3>
            <button onclick="closeModal()" style="background: none; border: none; font-size: 28px; font-weight: bold; color: white; cursor: pointer;">&times;</button>
        </div>
        <form id="modalForm" method="POST" style="padding: 25px;">
            <div id="modalFields"></div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <button type="button" onclick="closeModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> انصراف
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ذخیره
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalSlideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.tab-content { display: none; }
.tab-content.active { display: block; }
.tab-btn.active { 
    border-bottom: 3px solid #6C3CE1 !important; 
    color: #6C3CE1 !important; 
    font-weight: bold;
}
.modal .form-group { margin-bottom: 15px; }
.modal .form-label { display: block; margin-bottom: 5px; font-weight: 600; color: #374151; }
.modal .form-control, .modal .form-select { 
    width: 100%; padding: 8px 12px; 
    border: 1px solid #d1d5db; border-radius: 6px; 
    font-size: 14px;
}
.modal .form-control:focus, .modal .form-select:focus { 
    outline: none; border-color: #6C3CE1; 
    box-shadow: 0 0 0 3px rgba(108, 60, 225, 0.1);
}
.modal .form-row-grid { 
    display: grid; grid-template-columns: 1fr 1fr; gap: 15px; 
}
</style>

<script>
// ============================================================
// مدیریت تب‌ها با حفظ وضعیت در localStorage و URL
// ============================================================
function switchTab(event, tabId) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    
    event.currentTarget.classList.add('active');
    document.getElementById(tabId).classList.add('active');
    
    // ذخیره تب فعال در localStorage
    localStorage.setItem('pmbok_active_tab', tabId);
    
    // بروزرسانی URL با پارامتر tab
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.history.replaceState({}, '', url);
}

// بازیابی تب فعال هنگام بارگذاری صفحه
function restoreActiveTab() {
    // اولویت 1: پارامتر tab از URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabFromUrl = urlParams.get('tab');
    
    // اولویت 2: localStorage
    const savedTab = tabFromUrl || localStorage.getItem('pmbok_active_tab') || 'deliverables';
    
    const tabBtn = document.querySelector(`.tab-btn[data-tab="${savedTab}"]`);
    const tabContent = document.getElementById(savedTab);
    
    if (tabBtn && tabContent) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        tabBtn.classList.add('active');
        tabContent.classList.add('active');
    } else {
        // fallback به اولین تب
        document.querySelector('.tab-btn').classList.add('active');
        document.getElementById('deliverables').classList.add('active');
    }
}

// ============================================================
// مدیریت Modal
// ============================================================
function openModal() {
    document.getElementById('itemModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('itemModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    const modal = document.getElementById('itemModal');
    if (event.target == modal) closeModal();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});

// ============================================================
// باز کردن Modal برای افزودن
// ============================================================
function openAddModal(type) {
    const form = document.getElementById('modalForm');
    const title = document.getElementById('modalTitle');
    const fields = document.getElementById('modalFields');
    const projectId = <?= $project['id'] ?>;
    
    let action = '', html = '';
    
    switch(type) {
        case 'deliverable':
            action = `?controller=project&action=addDeliverable&id=${projectId}`;
            title.textContent = 'افزودن تحویل‌دادنی جدید';
            html = `
                <div class="form-group">
                    <label class="form-label">نام تحویل‌دادنی *</label>
                    <input type="text" name="deliverable_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <textarea name="deliverable_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select name="deliverable_status" class="form-select">
                            <option value="pending">در انتظار</option>
                            <option value="in_progress">در حال انجام</option>
                            <option value="completed">تکمیل شده</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاریخ برنامه‌ریزی</label>
                        <input type="date" name="deliverable_planned_date" class="form-control">
                    </div>
                </div>`;
            break;
            
        case 'risk':
            action = `?controller=project&action=addRisk&id=${projectId}`;
            title.textContent = 'افزودن ریسک جدید';
            html = `
                <div class="form-group">
                    <label class="form-label">عنوان ریسک *</label>
                    <input type="text" name="risk_title" class="form-control" required>
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">احتمال</label>
                        <select name="risk_probability" class="form-select">
                            <option value="very_low">بسیار کم</option>
                            <option value="low">کم</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="high">بالا</option>
                            <option value="very_high">بسیار بالا</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأثیر</label>
                        <select name="risk_impact" class="form-select">
                            <option value="very_low">بسیار کم</option>
                            <option value="low">کم</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="high">بالا</option>
                            <option value="very_high">بسیار بالا</option>
                        </select>
                    </div>
                </div>`;
            break;
            
        case 'stakeholder':
            action = `?controller=project&action=addStakeholder&id=${projectId}`;
            title.textContent = 'افزودن ذی‌نفع جدید';
            html = `
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">نام *</label>
                        <input type="text" name="stakeholder_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">نقش *</label>
                        <input type="text" name="stakeholder_role" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">ایمیل</label>
                    <input type="email" name="stakeholder_email" class="form-control">
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">نفوذ</label>
                        <select name="stakeholder_influence" class="form-select">
                            <option value="low">کم</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="high">بالا</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">علاقه‌مندی</label>
                        <select name="stakeholder_interest" class="form-select">
                            <option value="low">کم</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="high">بالا</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">وضعیت تعامل</label>
                    <select name="stakeholder_engagement" class="form-select">
                        <option value="unaware">ناآگاه</option>
                        <option value="resistant">مقاوم</option>
                        <option value="neutral" selected>خنثی</option>
                        <option value="supportive">حامی</option>
                        <option value="leading">رهبر</option>
                    </select>
                </div>`;
            break;
            
            case 'task':
            action = `?controller=project&action=updateProcess`;
            title.textContent = 'ویرایش فرآیند';
            
            // استخراج شناسه صحیح فرآیند (پوشش تمام حالت‌های ممکن نام‌گذاری در کوئری)
            const processId = data.process_id || data.task_id || data.id;
            
            html = `
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="process_id" value="${processId}">
                <input type="hidden" name="task_id" value="${processId}">
                <input type="hidden" name="project_id" value="${projectId}">
                
                <div class="form-group">
                    <label class="form-label">فرآیند</label>
                    <input type="text" class="form-control" value="${escapeHtml(data.task_code)} - ${escapeHtml(data.task_name)}" disabled>
                </div>
                
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-select">
                            <option value="not_started" ${data.status === 'not_started' ? 'selected' : ''}>شروع نشده</option>
                            <option value="in_progress" ${data.status === 'in_progress' ? 'selected' : ''}>در حال انجام</option>
                            <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>تکمیل شده</option>
                            <option value="deferred" ${data.status === 'deferred' ? 'selected' : ''}>تعویق‌شده</option>
                            <option value="na" ${data.status === 'na' ? 'selected' : ''}>نامرتبط (N/A)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">امتیاز کیفیت (0-100)</label>
                        <input type="number" name="quality_score" class="form-control" min="0" max="100" value="${data.quality_score || 0}">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">مسئول اجرا</label>
                    <input type="text" name="responsible_person" class="form-control" value="${escapeHtml(data.responsible_person || '')}" placeholder="نام شخص یا تیم مسئول">
                </div>

                <div class="form-group">
                    <label class="form-label">یادداشت / توضیحات</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="یادداشت‌های مربوط به این فرآیند...">${escapeHtml(data.notes || '')}</textarea>
                </div>`;
            break;
    }
    
    form.action = action;
    fields.innerHTML = html;
    openModal();
}

// ============================================================
// باز کردن Modal برای ویرایش - ✅ اصلاح شده
// ============================================================
function openEditModal(type, data) {
    const form = document.getElementById('modalForm');
    const title = document.getElementById('modalTitle');
    const fields = document.getElementById('modalFields');
    const projectId = <?= $project['id'] ?>;
    
    let action = '', html = '';
    
    switch(type) {
        case 'deliverable':
            action = `?controller=project&action=updateDeliverable`;
            title.textContent = 'ویرایش تحویل‌دادنی';
            html = `
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="project_id" value="${projectId}">
                <div class="form-group">
                    <label class="form-label">نام *</label>
                    <input type="text" name="name" class="form-control" value="${escapeHtml(data.name || '')}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">توضیحات</label>
                    <textarea name="description" class="form-control" rows="3">${escapeHtml(data.description || '')}</textarea>
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-select">
                            <option value="pending" ${data.status === 'pending' ? 'selected' : ''}>در انتظار</option>
                            <option value="in_progress" ${data.status === 'in_progress' ? 'selected' : ''}>در حال انجام</option>
                            <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>تکمیل شده</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تاریخ برنامه‌ریزی</label>
                        <input type="date" name="planned_date" class="form-control" value="${data.planned_date || ''}">
                    </div>
                </div>`;
            break;
            
        case 'risk':
            action = `?controller=project&action=updateRisk`;
            title.textContent = 'ویرایش ریسک';
            html = `
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="project_id" value="${projectId}">
                <div class="form-group">
                    <label class="form-label">عنوان ریسک *</label>
                    <input type="text" name="title" class="form-control" value="${escapeHtml(data.title || '')}" required>
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">احتمال</label>
                        <select name="probability" class="form-select">
                            <option value="very_low" ${data.probability === 'very_low' ? 'selected' : ''}>بسیار کم</option>
                            <option value="low" ${data.probability === 'low' ? 'selected' : ''}>کم</option>
                            <option value="medium" ${data.probability === 'medium' ? 'selected' : ''}>متوسط</option>
                            <option value="high" ${data.probability === 'high' ? 'selected' : ''}>بالا</option>
                            <option value="very_high" ${data.probability === 'very_high' ? 'selected' : ''}>بسیار بالا</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">تأثیر</label>
                        <select name="impact" class="form-select">
                            <option value="very_low" ${data.impact === 'very_low' ? 'selected' : ''}>بسیار کم</option>
                            <option value="low" ${data.impact === 'low' ? 'selected' : ''}>کم</option>
                            <option value="medium" ${data.impact === 'medium' ? 'selected' : ''}>متوسط</option>
                            <option value="high" ${data.impact === 'high' ? 'selected' : ''}>بالا</option>
                            <option value="very_high" ${data.impact === 'very_high' ? 'selected' : ''}>بسیار بالا</option>
                        </select>
                    </div>
                </div>`;
            break;
            
        case 'stakeholder':
            action = `?controller=project&action=updateStakeholder`;
            title.textContent = 'ویرایش ذی‌نفع';
            html = `
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="project_id" value="${projectId}">
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">نام *</label>
                        <input type="text" name="name" class="form-control" value="${escapeHtml(data.name || '')}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">نقش *</label>
                        <input type="text" name="role" class="form-control" value="${escapeHtml(data.role || '')}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">ایمیل</label>
                    <input type="email" name="email" class="form-control" value="${escapeHtml(data.email || '')}">
                </div>
                <div class="form-row-grid">
                    <div class="form-group">
                        <label class="form-label">نفوذ</label>
                        <select name="influence" class="form-select">
                            <option value="low" ${data.influence === 'low' ? 'selected' : ''}>کم</option>
                            <option value="medium" ${data.influence === 'medium' ? 'selected' : ''}>متوسط</option>
                            <option value="high" ${data.influence === 'high' ? 'selected' : ''}>بالا</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">علاقه‌مندی</label>
                        <select name="interest" class="form-select">
                            <option value="low" ${data.interest === 'low' ? 'selected' : ''}>کم</option>
                            <option value="medium" ${data.interest === 'medium' ? 'selected' : ''}>متوسط</option>
                            <option value="high" ${data.interest === 'high' ? 'selected' : ''}>بالا</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">وضعیت تعامل</label>
                    <select name="engagement_status" class="form-select">
                        <option value="unaware" ${data.engagement_status === 'unaware' ? 'selected' : ''}>ناآگاه</option>
                        <option value="resistant" ${data.engagement_status === 'resistant' ? 'selected' : ''}>مقاوم</option>
                        <option value="neutral" ${data.engagement_status === 'neutral' ? 'selected' : ''}>خنثی</option>
                        <option value="supportive" ${data.engagement_status === 'supportive' ? 'selected' : ''}>حامی</option>
                        <option value="leading" ${data.engagement_status === 'leading' ? 'selected' : ''}>رهبر</option>
                    </select>
                </div>`;
            break;
            
        case 'task':
            action = `?controller=project&action=updateProcess`;
            title.textContent = 'ویرایش فرآیند';
            html = `
                <input type="hidden" name="id" value="${data.id}">
                <input type="hidden" name="project_id" value="${projectId}">
                <div class="form-group">
                    <label class="form-label">فرآیند</label>
                    <input type="text" class="form-control" value="${escapeHtml(data.task_code)} - ${escapeHtml(data.task_name)}" disabled>
                </div>
                <div class="form-group">
                    <label class="form-label">وضعیت</label>
                    <select name="status" class="form-select">
                        <option value="not_started" ${data.status === 'not_started' ? 'selected' : ''}>شروع نشده</option>
                        <option value="in_progress" ${data.status === 'in_progress' ? 'selected' : ''}>در حال انجام</option>
                        <option value="completed" ${data.status === 'completed' ? 'selected' : ''}>تکمیل شده</option>
                        <option value="deferred" ${data.status === 'deferred' ? 'selected' : ''}>تعویق‌شده</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">یادداشت</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="یادداشت‌های مربوط به این فرآیند...">${escapeHtml(data.notes || '')}</textarea>
                </div>`;
            break;
    }
    
    form.action = action;
    fields.innerHTML = html;
    openModal();
}

// تابع کمکی برای جلوگیری از XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================================
// اجرا هنگام بارگذاری صفحه
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    restoreActiveTab();
});
</script>