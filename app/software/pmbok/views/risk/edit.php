<?php
/** ویرایش ریسک - PMBOK (بازطراحی‌شده) */
$pageTitle  = 'ویرایش ریسک - PMBOK';
$activePage = 'risk';

$projects = $projects ?? [];
$strategy = $strategy ?? ['enum' => false, 'options' => [], 'nullable' => true];
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$levels   = ['very_low' => 'بسیار کم', 'low' => 'کم', 'medium' => 'متوسط', 'high' => 'بالا', 'very_high' => 'بسیار بالا'];
$statuses = ['identified' => 'شناسایی شده', 'analyzed' => 'تحلیل شده', 'planned' => 'برنامه‌ریزی شده', 'implemented' => 'اجرا شده', 'closed' => 'بسته شده'];
?>

<div class="page-header">
    <div>
        <nav class="breadcrumb" aria-label="مسیر">
            <a href="?controller=risk">ریسک‌ها</a> / <span>ویرایش: <?= $e($risk['title']) ?></span>
        </nav>
        <h2><i class="fas fa-edit"></i> ویرایش ریسک</h2>
    </div>
</div>

<section class="card">
    <form method="POST" action="?controller=risk&action=edit&id=<?= (int)$risk['id'] ?>" class="standard-form">
        <div class="form-group">
            <label class="form-label" for="project_id">پروژه *</label>
            <select id="project_id" name="project_id" class="form-select" required>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= (int)$p['id'] === (int)$risk['project_id'] ? 'selected' : '' ?>><?= $e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="title">عنوان ریسک *</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= $e($risk['title']) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="description">توضیحات</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= $e($risk['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="probability">احتمال</label>
                <select id="probability" name="probability" class="form-select">
                    <?php foreach ($levels as $k => $v): ?><option value="<?= $k ?>" <?= ($risk['probability'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" for="impact">تاثیر</label>
                <select id="impact" name="impact" class="form-select">
                    <?php foreach ($levels as $k => $v): ?><option value="<?= $k ?>" <?= ($risk['impact'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">وضعیت</label>
            <select id="status" name="status" class="form-select">
                <?php foreach ($statuses as $k => $v): ?><option value="<?= $k ?>" <?= ($risk['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" for="response_strategy">استراتژی پاسخ</label>
            <?php if (!empty($strategy['enum'])): ?>
                <select id="response_strategy" name="response_strategy" class="form-select">
                    <?php if (!empty($strategy['nullable'])): ?><option value="">— انتخاب نشده —</option><?php endif; ?>
                    <?php foreach ($strategy['options'] as $opt): ?>
                        <option value="<?= $e($opt) ?>" <?= (string)($risk['response_strategy'] ?? '') === $opt ? 'selected' : '' ?>><?= $e(pmbok_getResponseStrategyLabel($opt)) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <textarea id="response_strategy" name="response_strategy" class="form-control" rows="2" placeholder="اجتناب / کاهش / انتقال / پذیرش"><?= $e((string)($risk['response_strategy'] ?? '')) ?></textarea>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="response_plan">برنامه پاسخ</label>
            <textarea id="response_plan" name="response_plan" class="form-control" rows="3"><?= $e($risk['response_plan'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="owner">مسئول</label>
            <input type="text" id="owner" name="owner" class="form-control" value="<?= $e($risk['owner'] ?? '') ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> ذخیره تغییرات</button>
            <a href="?controller=risk&action=show&id=<?= (int)$risk['id'] ?>" class="btn btn-secondary"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>
</section>
