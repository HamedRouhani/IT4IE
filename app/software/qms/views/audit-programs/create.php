<div class="container-fluid py-4">
    <div class="card shadow-sm" style="max-width:800px;margin:auto;">
        <div class="card-header bg-white"><h5 class="mb-0">ایجاد برنامه سالانه ممیزی</h5></div>
        <div class="card-body">
            <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=store">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">عنوان <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="مثال: برنامه ممیزی داخلی ۱۴۰۵">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">سال (شمسی)</label>
                        <select name="year" class="form-select">
                            <?php foreach ($jYears as $y): ?>
                                <option value="<?= $y ?>" <?= $y == $currentJYear ? 'selected' : '' ?>><?= fa_digits($y) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">شرح</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">اهداف</label>
                        <textarea name="objectives" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">دامنه کاربرد</label>
                        <textarea name="scope" rows="2" class="form-control" placeholder="کلیه واحدهای سازمانی"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">معیار ممیزی</label>
                        <input type="text" name="criteria" class="form-control" value="ISO 9001:2015">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">روش تعیین فراوانی</label>
                        <select name="frequency_method" class="form-select">
                            <option value="risk_based">مبتنی بر ریسک (توصیه شده)</option>
                            <option value="fixed">ثابت</option>
                            <option value="hybrid">ترکیبی</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary">ذخیره و ادامه به ارزیابی ریسک</button>
                    <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms" class="btn btn-outline-secondary">انصراف</a>
                </div>
            </form>
        </div>
    </div>
</div>