<?php
$pageTitle = $pageTitle ?? 'پروژه‌ها برای گزارش‌گیری';
$currentPage = $currentPage ?? 'report';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-file-export"></i> گزارش‌گیری و خروجی پروژه‌ها</h2>
        <p class="text-muted">مشاهده گزارش HTML یا دانلود خروجی برای MS Project و Primavera P6</p>
    </div>
    <div>
        <a href="?controller=report" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت به داشبورد
        </a>
    </div>
</div>

<?php if (empty($projects)): ?>
    <div class="card" style="text-align: center; padding: 40px;">
        <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--gray);"></i>
        <p style="margin-top: 15px;">هیچ پروژه‌ای برای گزارش‌گیری یافت نشد.</p>
        <a href="?controller=project&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> ایجاد پروژه جدید
        </a>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>نام پروژه</th>
                        <th>صنعت</th>
                        <th>فاز</th>
                        <th>متدولوژی</th>
                        <th>فرآیندها</th>
                        <th>ریسک‌ها</th>
                        <th style="width: 380px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $proj): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="?controller=project&action=show&id=<?= $proj['id'] ?>">
                                    <?= htmlspecialchars($proj['name']) ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php 
                            $industryLabels = [
                                'manufacturing' => 'تولیدی',
                                'oil_gas' => 'نفت و گاز',
                                'steel' => 'فولادی',
                                'fmcg' => 'FMCG',
                                'services' => 'خدماتی',
                                'it' => 'IT',
                                'construction' => 'ساخت‌وساز'
                            ];
                            ?>
                            <span class="badge badge-info">
                                <?= $industryLabels[$proj['industry'] ?? 'services'] ?? 'عمومی' ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?= pmbok_getPhaseColor($proj['phase']) ?>">
                                <?= pmbok_getPhaseLabel($proj['phase']) ?>
                            </span>
                        </td>
                        <td><?= pmbok_getMethodologyLabel($proj['methodology']) ?></td>
                        <td><span class="badge badge-primary"><?= $proj['task_count'] ?></span></td>
                        <td>
                            <span class="badge badge-<?= $proj['risk_count'] > 5 ? 'danger' : 'warning' ?>">
                                <?= $proj['risk_count'] ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="?controller=report&action=projectReport&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="مشاهده گزارش HTML قابل چاپ"
                                   target="_blank">
                                    <i class="fas fa-eye"></i> گزارش
                                </a>
                                <a href="?controller=report&action=exportToP6&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-success" 
                                   title="خروجی CSV برای Primavera P6">
                                    <i class="fas fa-file-csv"></i> P6
                                </a>
                                <a href="?controller=report&action=exportToMSProject&id=<?= $proj['id'] ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="خروجی XML برای MS Project">
                                    <i class="fas fa-file-code"></i> MSP
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- راهنمای استفاده -->
    <div class="card" style="margin-top: 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-right: 4px solid #0284c7;">
        <h4 style="margin-top: 0; color: #0369a1;">
            <i class="fas fa-info-circle"></i> راهنمای استفاده از خروجی‌ها
        </h4>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 15px;">
            <div>
                <strong style="color: #0369a1;">📊 خروجی P6 (Primavera)</strong>
                <p style="font-size: 0.9rem; margin: 5px 0 0 0; color: #475569;">
                    فایل CSV را در P6 از مسیر <code>File → Import → Spreadsheet</code> بارگذاری کنید.
                </p>
            </div>
            <div>
                <strong style="color: #0369a1;">📄 خروجی MSP (MS Project)</strong>
                <p style="font-size: 0.9rem; margin: 5px 0 0 0; color: #475569;">
                    فایل XML را در MS Project از مسیر <code>File → Open → XML Format</code> باز کنید.
                </p>
            </div>
            <div>
                <strong style="color: #0369a1;">👁️ گزارش HTML</strong>
                <p style="font-size: 0.9rem; margin: 5px 0 0 0; color: #475569;">
                    گزارش قابل چاپ در تب جدید باز می‌شود. با <code>Ctrl+P</code> به PDF تبدیل کنید.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>