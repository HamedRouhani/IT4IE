<?php
/**
 * لیست روش‌های حل مسائل OR
 * مسیر: app/software/or/views/method/index.php
 */
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-calculator text-primary"></i> روش‌های حل
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item active">روش‌های حل</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- فرم فیلتر -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <!-- تغییر ۱: action خالی است تا به آدرس فعلی ارسال شود -->
            <!-- تغییر ۲: اضافه کردن input مخفی برای حفظ controller -->
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="controller" value="method">
                
                <div class="col-md-5">
                    <label for="problem_type_id" class="form-label">فیلتر بر اساس نوع مسئله</label>
                    <select name="problem_type_id" id="problem_type_id" class="form-select">
                        <option value="">همه انواع مسئله</option>
                        <?php foreach ($problemTypes as $pt): ?>
                            <option value="<?= $pt['id'] ?>" <?= ($filterType == $pt['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pt['name_fa']) ?> (<?= $pt['code'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label for="category" class="form-label">فیلتر بر اساس دسته‌بندی</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">همه دسته‌ها</option>
                        <option value="initial" <?= ($filterCat === 'initial') ? 'selected' : '' ?>>روش اولیه (Initial)</option>
                        <option value="optimization" <?= ($filterCat === 'optimization') ? 'selected' : '' ?>>بهینه‌سازی (Optimization)</option>
                        <option value="exact" <?= ($filterCat === 'exact') ? 'selected' : '' ?>>دقیق (Exact)</option>
                        <option value="heuristic" <?= ($filterCat === 'heuristic') ? 'selected' : '' ?>>ابتکاری (Heuristic)</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-or-primary w-100">
                        <i class="fas fa-filter"></i> اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- لیست روش‌ها -->
    <?php if (!empty($methods)): ?>
        <div class="row g-4">
            <?php foreach ($methods as $method): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="mb-0"><?= htmlspecialchars($method['name_fa']) ?></h5>
                                <span class="badge bg-primary"><?= htmlspecialchars($method['code']) ?></span>
                            </div>
                            
                            <p class="text-muted small mb-3" style="min-height: 60px;">
                                <?php 
                                $desc = $method['description'] ?? 'توضیحاتی برای این روش ثبت نشده است.';
                                echo htmlspecialchars(mb_substr($desc, 0, 120) . (mb_strlen($desc) > 120 ? '...' : '')); 
                                ?>
                            </p>

                            <div class="mb-3">
                                <span class="badge bg-light text-dark border me-2">
                                    <i class="fas fa-cubes"></i> 
                                    <?= or_getProblemTypeLabel($method['problem_type_code'] ?? '') ?>
                                </span>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-tag"></i> 
                                    <?= or_getMethodCategoryLabel($method['category'] ?? '') ?>
                                </span>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pt-0 pb-3">
                            <a href="<?= or_url('controller=method&action=show&id=' . $method['id']) ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-eye"></i> مشاهده جزئیات و آموزش
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="text-muted mb-3">هیچ روشی با این فیلترها یافت نشد</h4>
                <p class="text-muted mb-4">لطفاً فیلترها را تغییر دهید یا پاک کنید.</p>
                <a href="<?= or_url('controller=method') ?>" class="btn btn-primary">
                    <i class="fas fa-redo"></i> نمایش همه روش‌ها
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>