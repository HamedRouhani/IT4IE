<div class="container-fluid py-4">
    <div class="card shadow-sm" style="max-width:800px;margin:auto;">
        <div class="card-header bg-white"><h5 class="mb-0">ویرایش: <?= qms_e($program['title']) ?></h5></div>
        <div class="card-body">
            <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=update&id=<?= $program['id'] ?>">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">عنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= qms_e($program['title']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">سال (شمسی)</label>
                        <select name="year" class="form-select">
                            <?php foreach ($jYears as $y): ?>
                                <option value="<?= $y ?>" <?= $y == (int)$program['year'] ? 'selected' : '' ?>><?= fa_digits($y) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">شرح</label>
                        <textarea name="description" rows="2" class="form-control"><?= qms_e($program['description']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اهداف</label>
                        <textarea name="objectives" rows="2" class="form-control"><?= qms_e($program['objectives']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">دامنه</label>
                        <textarea name="scope" rows="2" class="form-control"><?= qms_e($program['scope']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">معیار</label>
                        <input type="text" name="criteria" class="form-control" value="<?= qms_e($program['criteria']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">روش فراوانی</label>
                        <select name="frequency_method" class="form-select">
                            <?php foreach (['risk_based'=>'مبتنی بر ریسک','fixed'=>'ثابت','hybrid'=>'ترکیبی'] as $k=>$l): ?>
                                <option value="<?= $k ?>" <?= $program['frequency_method']===$k?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">وضعیت</label>
                        <select name="status" class="form-select">
                            <?php foreach (['draft'=>'پیش‌نویس','approved'=>'تأیید شده','active'=>'فعال','completed'=>'تکمیل شده','archived'=>'بایگانی'] as $k=>$l): ?>
                                <option value="<?= $k ?>" <?= $program['status']===$k?'selected':'' ?>><?= $l ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2 justify-content-between">
                    <button class="btn btn-primary">ذخیره تغییرات</button>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=show&id=<?= $program['id'] ?>" class="btn btn-outline-secondary">بازگشت</a>
                </div>
            </form>
        </div>
    </div>
</div>