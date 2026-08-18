<?php
/**
 * صفحه اصلی گزارش‌های هوشمند
 * مسیر: app/software/babok/views/reports/index.php
 */
?>

<div style="max-width: 1100px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 10px 0; font-size: 1.8rem;">
            <i class="fas fa-file-alt"></i> گزارش‌های هوشمند پروژه
        </h2>
        <p style="margin: 0; opacity: 0.95; font-size: 1rem;">
            پروژه مورد نظر را انتخاب کنید تا گزارش کامل، قابل پرینت یا خروجی Excel تولید شود.
        </p>
    </div>

    <?php if (empty($projects)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
            <i class="fas fa-folder-open" style="font-size: 3rem; color: #94a3b8; margin-bottom: 15px;"></i>
            <h3 style="color: #475569; margin: 0 0 10px 0;">هنوز پروژه‌ای ایجاد نکرده‌اید</h3>
            <p style="color: #64748b; margin: 0 0 20px 0;">برای تولید گزارش، ابتدا یک پروژه بسازید.</p>
            <a href="?route=projects_create" class="btn btn-primary">
                <i class="fas fa-plus"></i> ایجاد پروژه جدید
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
            <?php foreach ($projects as $project): ?>
                <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.04)'">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div>
                            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.1rem;">
                                <?= htmlspecialchars($project['name']) ?>
                            </h3>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <span style="background: #e0f2fe; color: #0369a1; padding: 3px 10px; border-radius: 99px; font-size: 0.75rem;">
                                    <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?>
                                </span>
                                <span style="background: #f1f5f9; color: #475569; padding: 3px 10px; border-radius: 99px; font-size: 0.75rem;">
                                    <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?>
                                </span>
                            </div>
                        </div>
                        <div style="text-align: center; background: #f8fafc; padding: 8px 12px; border-radius: 8px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #6C3CE1;"><?= $project['task_count'] ?></div>
                            <div style="font-size: 0.7rem; color: #64748b;">وظیفه</div>
                        </div>
                    </div>

                    <?php if (!empty($project['description'])): ?>
                        <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 15px 0; line-height: 1.5;">
                            <?= htmlspecialchars(mb_substr($project['description'], 0, 100)) ?><?= mb_strlen($project['description']) > 100 ? '...' : '' ?>
                        </p>
                    <?php endif; ?>

                    <div style="display: flex; flex-direction: column; gap: 8px; border-top: 1px solid #f1f5f9; padding-top: 15px;">
                        <a href="?route=reports_project&id=<?= $project['id'] ?>" 
                           style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: #6C3CE1; color: white; text-decoration: none; border-radius: 8px; font-size: 0.9rem; font-weight: 600; transition: background 0.2s;"
                           onmouseover="this.style.background='#5b21b6'" onmouseout="this.style.background='#6C3CE1'">
                            <i class="fas fa-file-alt"></i>
                            <span>گزارش کامل پروژه</span>
                        </a>
                        
                        <div style="display: flex; gap: 8px;">
                            <a href="?route=reports_export_tasks&id=<?= $project['id'] ?>" 
                               style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 500; transition: background 0.2s;"
                               onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                                <i class="fas fa-file-csv"></i>
                                <span>خروجی وظایف</span>
                            </a>
                            <a href="?route=reports_export_traceability&id=<?= $project['id'] ?>" 
                               style="flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 8px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 0.8rem; font-weight: 500; transition: background 0.2s;"
                               onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'">
                                <i class="fas fa-link"></i>
                                <span>خروجی ردیابی</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>