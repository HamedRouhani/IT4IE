<?php
/**
 * جزئیات پروژه - PMBOK Analyzer (نسخهٔ بازطراحی‌شده)
 * - بدون استایل inline و بدون onclick؛ رویدادها با data-attribute
 * - یک modal استاندارد (<dialog>) و فرم‌ها از روی یک schema ساخته می‌شوند
 */
$pageTitle   = ($project['name'] ?? 'پروژه') . ' - PMBOK';
$currentPage = $currentPage ?? 'project';

$pid          = (int)$project['id'];
$deliverables = $deliverables ?? [];
$risks        = $risks ?? [];
$stakeholders = $stakeholders ?? [];
$projectTasks = $projectTasks ?? [];
$allTasks     = $allTasks ?? [];

$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// داده‌ی ردیف برای دکمهٔ ویرایش (در data-item؛ امن در برابر نقل‌قول‌ها)
$itemAttr = static fn(array $row) => htmlspecialchars(
    json_encode($row, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT),
    ENT_QUOTES, 'UTF-8'
);

// سلول عملیات (ویرایش + حذف)
$rowActions = function (string $type, array $row, string $deleteAction, string $idField, string $confirm) use ($pid, $e, $itemAttr) {
    ob_start(); ?>
    <td class="col-actions">
        <button type="button" class="btn btn-sm btn-info" data-edit="<?= $type ?>"
                data-item="<?= $itemAttr($row) ?>" title="ویرایش" aria-label="ویرایش">
            <i class="fas fa-edit"></i>
        </button>
        <form method="POST" class="inline-form-btn"
              action="?controller=project&action=<?= $e($deleteAction) ?>&id=<?= $pid ?>"
              data-confirm="<?= $e($confirm) ?>">
            <input type="hidden" name="<?= $e($idField) ?>" value="<?= (int)$row['id'] ?>">
            <input type="hidden" name="project_id" value="<?= $pid ?>">
            <button type="submit" class="btn btn-sm btn-danger" title="حذف" aria-label="حذف">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
    <?php return ob_get_clean();
};

$delStatus  = ['pending' => ['در انتظار', 'secondary'], 'in_progress' => ['در حال انجام', 'warning'], 'completed' => ['تکمیل شده', 'success']];
$taskStatus = ['not_started' => 'secondary', 'in_progress' => 'warning', 'completed' => 'success', 'deferred' => 'danger'];

// فرآیندهایی که هنوز به پروژه اضافه نشده‌اند (برای modal افزودن)
$usedTaskIds = array_map('intval', array_column($projectTasks, 'task_id'));
$availableTasks = [];
foreach ($allTasks as $t) {
    if (!in_array((int)$t['id'], $usedTaskIds, true)) {
        $availableTasks[] = ['id' => (int)$t['id'], 'code' => $t['code'] ?? '', 'name' => $t['name'] ?? '', 'ka' => $t['ka_name'] ?? ''];
    }
}
?>

<div class="page-header">
    <div>
        <nav class="breadcrumb" aria-label="مسیر">
            <a href="?controller=project">پروژه‌ها</a> /
            <span><?= $e($project['name']) ?></span>
        </nav>
        <h2><i class="fas fa-project-diagram"></i> <?= $e($project['name']) ?></h2>
    </div>
    <div class="header-actions">
        <a href="?controller=project&action=analytics&id=<?= $pid ?>" class="btn btn-primary"><i class="fas fa-chart-line"></i> تحلیل ارزش کسب‌شده</a>
        <a href="?controller=project&action=edit&id=<?= $pid ?>" class="btn btn-warning"><i class="fas fa-edit"></i> ویرایش پروژه</a>
        <a href="?controller=report&action=exportMsProject&id=<?= $pid ?>" class="btn btn-success"><i class="fas fa-file-code"></i> خروجی MSP</a>
        <a href="?controller=report&action=exportPrimavera&id=<?= $pid ?>" class="btn btn-info"><i class="fas fa-file-csv"></i> خروجی P6</a>
    </div>
</div>

<!-- اطلاعات پروژه -->
<section class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پروژه</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">فاز</span>
            <span class="badge badge-<?= pmbok_getPhaseColor($project['phase']) ?>"><?= pmbok_getPhaseLabel($project['phase']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">متدولوژی</span>
            <span class="badge badge-info"><?= pmbok_getMethodologyLabel($project['methodology']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">صنعت</span>
            <span class="badge"><?= $e($project['industry'] ?? 'عمومی') ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">تاریخ ایجاد</span>
            <span><?= pmbok_showDate($project['created_at']) ?></span>
        </div>
    </div>
    <?php if (!empty($project['description'])): ?>
        <div class="card-footer">
            <strong>توضیحات</strong>
            <p class="text-muted"><?= nl2br($e($project['description'])) ?></p>
        </div>
    <?php endif; ?>
</section>

<!-- تب‌ها -->
<div class="tabs-container">
    <div class="tabs" role="tablist">
        <button type="button" class="tab-btn" role="tab" data-tab="deliverables"><i class="fas fa-box"></i> تحویل‌دادنی‌ها (<?= count($deliverables) ?>)</button>
        <button type="button" class="tab-btn" role="tab" data-tab="risks"><i class="fas fa-exclamation-triangle"></i> ریسک‌ها (<?= count($risks) ?>)</button>
        <button type="button" class="tab-btn" role="tab" data-tab="stakeholders"><i class="fas fa-users"></i> ذی‌نفعان (<?= count($stakeholders) ?>)</button>
        <button type="button" class="tab-btn" role="tab" data-tab="tasks"><i class="fas fa-tasks"></i> فرآیندها (<?= count($projectTasks) ?>)</button>
    </div>

    <!-- تب ۱: تحویل‌دادنی‌ها -->
    <div class="tab-content" id="deliverables" role="tabpanel">
        <section class="card">
            <div class="card-head">
                <h3 class="card-title"><i class="fas fa-box"></i> تحویل‌دادنی‌ها</h3>
                <button type="button" class="btn btn-primary" data-add="deliverable"><i class="fas fa-plus"></i> افزودن تحویل‌دادنی</button>
            </div>
            <?php if (empty($deliverables)): ?>
                <p class="empty-note">هیچ تحویل‌دادنی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>نام</th><th>توضیحات</th><th>وضعیت</th><th>تاریخ برنامه‌ریزی</th><th class="col-actions">عملیات</th></tr></thead>
                        <tbody>
                        <?php foreach ($deliverables as $d): [$sLabel, $sColor] = $delStatus[$d['status']] ?? [$d['status'], 'secondary']; ?>
                            <tr>
                                <td><strong><?= $e($d['name']) ?></strong></td>
                                <td><?= $e(pmbok_truncateText($d['description'] ?? '', 50)) ?></td>
                                <td><span class="badge badge-<?= $sColor ?>"><?= $e($sLabel) ?></span></td>
                                <td><?= pmbok_showDate($d['planned_date'] ?? '') ?></td>
                                <?= $rowActions('deliverable', $d, 'deleteDeliverable', 'id', 'آیا از حذف این تحویل‌دادنی اطمینان دارید؟') ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- تب ۲: ریسک‌ها -->
    <div class="tab-content" id="risks" role="tabpanel">
        <section class="card">
            <div class="card-head">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> ریسک‌ها</h3>
                <button type="button" class="btn btn-danger" data-add="risk"><i class="fas fa-plus"></i> افزودن ریسک</button>
            </div>
            <?php if (empty($risks)): ?>
                <p class="empty-note">هیچ ریسکی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>عنوان</th><th>احتمال</th><th>تأثیر</th><th>امتیاز</th><th>وضعیت</th><th class="col-actions">عملیات</th></tr></thead>
                        <tbody>
                        <?php foreach ($risks as $r): $score = (int)$r['risk_score']; $tone = $score >= 15 ? 'high' : ($score >= 8 ? 'mid' : 'low'); ?>
                            <tr>
                                <td><?= $e($r['title']) ?></td>
                                <td><span class="badge"><?= pmbok_getProbabilityLabel($r['probability']) ?></span></td>
                                <td><span class="badge"><?= pmbok_getImpactLabel($r['impact']) ?></span></td>
                                <td><strong class="score score--<?= $tone ?>"><?= $score ?></strong></td>
                                <td><span class="badge badge-info"><?= pmbok_getRiskStatusLabel($r['status']) ?></span></td>
                                <?= $rowActions('risk', $r, 'deleteRisk', 'risk_id', 'آیا از حذف این ریسک اطمینان دارید؟') ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- تب ۳: ذی‌نفعان -->
    <div class="tab-content" id="stakeholders" role="tabpanel">
        <section class="card">
            <div class="card-head">
                <h3 class="card-title"><i class="fas fa-users"></i> ذی‌نفعان</h3>
                <button type="button" class="btn btn-primary" data-add="stakeholder"><i class="fas fa-user-plus"></i> افزودن ذی‌نفع</button>
            </div>
            <?php if (empty($stakeholders)): ?>
                <p class="empty-note">هیچ ذی‌نفعی ثبت نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>نام</th><th>نقش</th><th>ایمیل</th><th>نفوذ</th><th>علاقه</th><th>وضعیت تعامل</th><th class="col-actions">عملیات</th></tr></thead>
                        <tbody>
                        <?php foreach ($stakeholders as $s): ?>
                            <tr>
                                <td><strong><?= $e($s['name']) ?></strong></td>
                                <td><?= $e($s['role']) ?></td>
                                <td><?= $e($s['email'] ?? '-') ?></td>
                                <td><span class="badge"><?= $e($s['influence']) ?></span></td>
                                <td><span class="badge"><?= $e($s['interest']) ?></span></td>
                                <td><span class="badge badge-info"><?= $e($s['engagement_status'] ?? 'neutral') ?></span></td>
                                <?= $rowActions('stakeholder', $s, 'deleteStakeholder', 'stakeholder_id', 'آیا از حذف این ذی‌نفع اطمینان دارید؟') ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- تب ۴: فرآیندها -->
    <div class="tab-content" id="tasks" role="tabpanel">
        <section class="card">
            <div class="card-head">
                <h3 class="card-title"><i class="fas fa-tasks"></i> فرآیندها</h3>
                <button type="button" class="btn btn-success" data-add="task"><i class="fas fa-plus"></i> افزودن فرآیند</button>
            </div>
            <?php if (empty($projectTasks)): ?>
                <p class="empty-note">هیچ فرآیندی اضافه نشده است.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>کد</th><th>نام فرآیند</th><th>حوزه دانشی</th><th>وضعیت</th><th>هزینهٔ واقعی تجمعی تا امروز (<?= $e($project['currency'] ?? 'IRR') ?>)</th><th>یادداشت</th><th class="col-actions">عملیات</th></tr></thead>
                        <tbody>
                        <?php foreach ($projectTasks as $pt): ?>
                            <tr>
                                <td><code><?= $e($pt['task_code']) ?></code></td>
                                <td><?= $e($pt['task_name']) ?></td>
                                <td><?= $e($pt['ka_name']) ?></td>
                                <td><span class="badge badge-<?= $taskStatus[$pt['status']] ?? 'secondary' ?>"><?= pmbok_getTaskStatusLabel($pt['status']) ?></span></td>
                                <td><?= isset($pt['actual_cost']) && $pt['actual_cost'] !== null ? number_format((float)$pt['actual_cost'], 0) : '—' ?></td>
                                <td><?= $e(pmbok_truncateText($pt['notes'] ?? '-', 40)) ?></td>
                                <?= $rowActions('task', $pt, 'deleteTask', 'pt_id', 'آیا از حذف این فرآیند اطمینان دارید؟') ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- Modal عمومی (native dialog: Esc، focus-trap و backdrop داخلی) -->
<dialog id="itemModal" class="pm-modal" aria-labelledby="modalTitle">
    <form id="modalForm" method="POST">
        <header class="pm-modal-head">
            <h3 id="modalTitle">افزودن</h3>
            <button type="button" class="pm-modal-close" data-close aria-label="بستن">&times;</button>
        </header>
        <div id="modalFields" class="pm-modal-body"></div>
        <footer class="pm-modal-foot">
            <button type="button" class="btn btn-secondary" data-close><i class="fas fa-times"></i> انصراف</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ذخیره</button>
        </footer>
    </form>
</dialog>

<script>
(function () {
    'use strict';
    var PID = <?= $pid ?>;
    var AVAILABLE_TASKS = <?= json_encode($availableTasks, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    /* ---------- گزینه‌های مشترک ---------- */
    var LEVEL5 = [['very_low','بسیار کم'],['low','کم'],['medium','متوسط'],['high','بالا'],['very_high','بسیار بالا']];
    var LEVEL3 = [['low','کم'],['medium','متوسط'],['high','بالا']];
    var ENGAGE = [['unaware','ناآگاه'],['resistant','مقاوم'],['neutral','خنثی'],['supportive','حامی'],['leading','رهبر']];
    var TASK_STATUS = [['not_started','شروع نشده'],['in_progress','در حال انجام'],['completed','تکمیل شده'],['deferred','تعویق‌شده']];

    /* ---------- schema فرم‌ها ----------
       key = نام ستون/فیلد در ویرایش | add = نام فیلد POST در افزودن | full = تمام‌عرض */
    var FORMS = {
        deliverable: {
            addTitle: 'افزودن تحویل‌دادنی جدید', editTitle: 'ویرایش تحویل‌دادنی',
            addAction: 'addDeliverable&id=' + PID, editAction: 'updateDeliverable',
            fields: [
                { key: 'name', add: 'deliverable_name', label: 'نام *', type: 'text', req: true, full: true },
                { key: 'description', add: 'deliverable_description', label: 'توضیحات', type: 'textarea', full: true },
                { key: 'status', add: 'deliverable_status', label: 'وضعیت', type: 'select', def: 'pending',
                  opts: [['pending','در انتظار'],['in_progress','در حال انجام'],['completed','تکمیل شده']] },
                { key: 'planned_date', add: 'deliverable_planned_date', label: 'تاریخ برنامه‌ریزی', type: 'date' }
            ]
        },
        risk: {
            addTitle: 'افزودن ریسک جدید', editTitle: 'ویرایش ریسک',
            addAction: 'addRisk&id=' + PID, editAction: 'updateRisk',
            fields: [
                { key: 'title', add: 'risk_title', label: 'عنوان ریسک *', type: 'text', req: true, full: true },
                { key: 'probability', add: 'risk_probability', label: 'احتمال', type: 'select', opts: LEVEL5, def: 'medium' },
                { key: 'impact', add: 'risk_impact', label: 'تأثیر', type: 'select', opts: LEVEL5, def: 'medium' }
            ]
        },
        stakeholder: {
            addTitle: 'افزودن ذی‌نفع جدید', editTitle: 'ویرایش ذی‌نفع',
            addAction: 'addStakeholder&id=' + PID, editAction: 'updateStakeholder',
            fields: [
                { key: 'name', add: 'stakeholder_name', label: 'نام *', type: 'text', req: true },
                { key: 'role', add: 'stakeholder_role', label: 'نقش *', type: 'text', req: true },
                { key: 'email', add: 'stakeholder_email', label: 'ایمیل', type: 'email', full: true },
                { key: 'influence', add: 'stakeholder_influence', label: 'نفوذ', type: 'select', opts: LEVEL3, def: 'medium' },
                { key: 'interest', add: 'stakeholder_interest', label: 'علاقه‌مندی', type: 'select', opts: LEVEL3, def: 'medium' },
                { key: 'engagement_status', add: 'stakeholder_engagement', label: 'وضعیت تعامل', type: 'select', opts: ENGAGE, def: 'neutral', full: true }
            ]
        },
        task: {
            addTitle: 'افزودن فرآیند', editTitle: 'ویرایش فرآیند',
            addAction: 'addTask&id=' + PID, editAction: 'updateProcess',
            addFields: [
                { add: 'task_id', label: 'فرآیند *', type: 'select', req: true, full: true, placeholder: 'یک فرآیند انتخاب کنید…',
                  opts: AVAILABLE_TASKS.map(function (t) { return [String(t.id), t.code + ' - ' + t.name + ' (' + t.ka + ')']; }) },
                { add: 'task_status', label: 'وضعیت', type: 'select', def: 'not_started', full: true, opts: TASK_STATUS }
            ],
            editFields: [
                { label: 'فرآیند', type: 'text', disabled: true, full: true,
                  value: function (d) { return (d.task_code || '') + ' - ' + (d.task_name || ''); } },
                { key: 'status', label: 'وضعیت', type: 'select', full: true, opts: TASK_STATUS },
                { key: 'actual_cost', label: 'هزینهٔ واقعی تجمعی تا امروز (' + <?= json_encode($project['currency'] ?? 'IRR', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ')', type: 'number', full: true, min: '0', step: '0.01', placeholder: 'خالی = ثبت‌نشده؛ برای هزینهٔ صفر، عدد ۰ وارد کنید' },
                { key: 'notes', label: 'یادداشت', type: 'textarea', full: true, placeholder: 'یادداشت‌های مربوط به این فرآیند…' }
            ]
        }
    };

    /* ---------- ساخت DOM (بدون innerHTML برای مقادیر کاربر؛ XSS-safe) ---------- */
    function el(tag, attrs, children) {
        var n = document.createElement(tag);
        Object.keys(attrs || {}).forEach(function (k) {
            var v = attrs[k];
            if (v === false || v == null) return;
            if (k === 'text') n.textContent = v; else n.setAttribute(k, v === true ? '' : v);
        });
        (children || []).forEach(function (c) { n.appendChild(c); });
        return n;
    }
    function hidden(name, value) { return el('input', { type: 'hidden', name: name, value: value }); }

    var uid = 0;
    function buildField(f, data, mode) {
        var name = mode === 'edit' ? (f.key || null) : (f.add || null);
        var id = 'f_' + (++uid);
        var val = f.value ? f.value(data || {}) : (mode === 'edit' && data && f.key ? data[f.key] : f.def);
        if (val == null) val = f.def != null ? f.def : '';
        if (f.type === 'date') val = String(val).slice(0, 10);

        var ctrl;
        if (f.type === 'select') {
            ctrl = el('select', { id: id, name: name, class: 'form-select', required: !!f.req });
            if (f.placeholder) ctrl.appendChild(el('option', { value: '', text: f.placeholder }));
            f.opts.forEach(function (o) {
                var opt = el('option', { value: o[0], text: o[1] });
                if (String(o[0]) === String(val)) opt.selected = true;
                ctrl.appendChild(opt);
            });
        } else if (f.type === 'textarea') {
            ctrl = el('textarea', { id: id, name: name, class: 'form-control', rows: 3, placeholder: f.placeholder });
            ctrl.value = val;
        } else {
            ctrl = el('input', { id: id, type: f.type, name: f.disabled ? null : name, class: 'form-control',
                                 required: !!f.req, disabled: !!f.disabled, min: f.min, max: f.max, step: f.step });
            ctrl.value = val;
        }
        return el('div', { class: 'form-group' + (f.full ? ' form-group--full' : '') }, [
            el('label', { class: 'form-label', for: id, text: f.label }), ctrl
        ]);
    }

    /* ---------- Modal ---------- */
    var modal = document.getElementById('itemModal');
    var form = document.getElementById('modalForm');
    var fieldsBox = document.getElementById('modalFields');
    var titleEl = document.getElementById('modalTitle');

    function openForm(type, mode, data) {
        var cfg = FORMS[type];
        var fields = mode === 'edit' ? (cfg.editFields || cfg.fields) : (cfg.addFields || cfg.fields);
        fieldsBox.textContent = '';
        titleEl.textContent = mode === 'edit' ? cfg.editTitle : cfg.addTitle;
        form.action = '?controller=project&action=' + (mode === 'edit' ? cfg.editAction : cfg.addAction);

        if (mode === 'edit') {
            fieldsBox.appendChild(hidden('id', data.id));
            fieldsBox.appendChild(hidden('project_id', PID));
        }
        if (type === 'task' && mode === 'add' && !AVAILABLE_TASKS.length) {
            fieldsBox.appendChild(el('p', { class: 'text-muted form-group--full', text: 'همهٔ فرآیندها قبلاً به این پروژه اضافه شده‌اند.' }));
        } else {
            fields.forEach(function (f) { fieldsBox.appendChild(buildField(f, data, mode)); });
        }
        modal.showModal();
        var first = fieldsBox.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
        if (first) first.focus();
    }

    document.addEventListener('click', function (ev) {
        var add = ev.target.closest('[data-add]');
        if (add) return openForm(add.getAttribute('data-add'), 'add', null);

        var edit = ev.target.closest('[data-edit]');
        if (edit) {
            var item = {};
            try { item = JSON.parse(edit.getAttribute('data-item')); } catch (e) { /* داده خراب */ }
            return openForm(edit.getAttribute('data-edit'), 'edit', item);
        }
        if (ev.target.closest('[data-close]') || ev.target === modal) modal.close();
    });

    // تأیید حذف
    document.addEventListener('submit', function (ev) {
        var msg = ev.target.getAttribute && ev.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) ev.preventDefault();
    });

    /* ---------- تب‌ها (URL ← localStorage ← پیش‌فرض) ---------- */
    var tabBtns = Array.prototype.slice.call(document.querySelectorAll('.tab-btn[data-tab]'));

    function activate(id, persist) {
        var btn = tabBtns.filter(function (b) { return b.getAttribute('data-tab') === id; })[0];
        var panel = id ? document.getElementById(id) : null;
        if (!btn || !panel) return false;
        tabBtns.forEach(function (b) {
            var on = b === btn;
            b.classList.toggle('active', on);
            b.setAttribute('aria-selected', String(on));
        });
        document.querySelectorAll('.tab-content').forEach(function (p) { p.classList.toggle('active', p === panel); });
        if (persist) {
            try { localStorage.setItem('pmbok_active_tab', id); } catch (e) { /* storage ممکن است در دسترس نباشد */ }
            var url = new URL(window.location);
            url.searchParams.set('tab', id);
            window.history.replaceState({}, '', url);
        }
        return true;
    }

    tabBtns.forEach(function (b) {
        b.addEventListener('click', function () { activate(b.getAttribute('data-tab'), true); });
    });

    var saved = null;
    try { saved = localStorage.getItem('pmbok_active_tab'); } catch (e) { /* ignore */ }
    var fromUrl = new URLSearchParams(window.location.search).get('tab');
    activate(fromUrl, false) || activate(saved, false) || activate('deliverables', false);
})();
</script>
