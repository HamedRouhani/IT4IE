<?php
/**
 * ویو مشاهده جزئیات پروژه
 * مسیر: app/software/babok/views/projects/view.php
 */
$pageTitle = $project['name'] . ' - BABOK Analyzer';
$activePage = 'projects';
$progressPercentage = $progress['completion_percentage'] ?? 0;
?>

<!-- هدر پروژه -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-folder-open"></i> <?= htmlspecialchars($project['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-success">
                <i class="fas fa-calendar-check"></i> برنامه‌ریزی وظایف
            </a>
            <a href="?route=projects_edit&id=<?= $project['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> ویرایش پروژه
            </a>
            <a href="?route=projects" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <div class="row" style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
        <div class="card" style="flex: 1; min-width: 150px; margin-bottom: 0; padding: 15px; text-align: center;">
            <h5 class="text-muted" style="margin: 0 0 10px 0;">فاز فعلی</h5>
            <span class="badge badge-secondary" style="font-size: 0.9rem;">
                <?= \App\Software\Babok\Helpers\Utils::phaseLabel($project['phase']) ?>
            </span>
        </div>
        <div class="card" style="flex: 1; min-width: 150px; margin-bottom: 0; padding: 15px; text-align: center;">
            <h5 class="text-muted" style="margin: 0 0 10px 0;">متدولوژی</h5>
            <span class="badge methodology-<?= $project['methodology'] ?>" style="font-size: 0.9rem;">
                <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology']) ?>
            </span>
        </div>
        <div class="card" style="flex: 1; min-width: 150px; margin-bottom: 0; padding: 15px; text-align: center;">
            <h5 class="text-muted" style="margin: 0 0 10px 0;">تعداد ذی‌نفعان</h5>
            <strong style="font-size: 1.5rem;"><?= $project['stakeholder_count'] ?></strong>
        </div>
        <div class="card" style="flex: 1; min-width: 150px; margin-bottom: 0; padding: 15px; text-align: center;">
            <h5 class="text-muted" style="margin: 0 0 10px 0;">تاریخ ایجاد</h5>
            <strong><?= date('Y-m-d', strtotime($project['created_at'])) ?></strong>
        </div>
    </div>
    
    <?php if (!empty($project['description'])): ?>
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h5 style="margin-bottom: 8px;"><i class="fas fa-align-right"></i> توضیحات</h5>
            <p style="margin: 0; line-height: 1.8;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
        </div>
    <?php endif; ?>
</div>

<!-- پیشرفت پروژه -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title"><i class="fas fa-chart-pie"></i> پیشرفت پروژه</h3>
    
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        <div style="text-align: center; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #6c757d;"><?= $progress['total'] ?? 0 ?></div>
            <div class="text-muted">کل وظایف</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #d4edda; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #28a745;"><?= $progress['completed'] ?? 0 ?></div>
            <div class="text-muted">تکمیل شده</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #fff3cd; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #ffc107;"><?= $progress['in_progress'] ?? 0 ?></div>
            <div class="text-muted">در حال انجام</div>
        </div>
        <div style="text-align: center; padding: 15px; background: #f8d7da; border-radius: 8px;">
            <div style="font-size: 2rem; font-weight: 700; color: #dc3545;"><?= $progress['not_started'] ?? 0 ?></div>
            <div class="text-muted">انجام نشده</div>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <span>پیشرفت کلی</span>
            <strong><?= $progressPercentage ?>%</strong>
        </div>
        <div class="progress" style="height: 12px; background: #e9ecef; border-radius: 6px; overflow: hidden;">
            <div class="progress-bar" style="width: <?= $progressPercentage ?>%; background: #28a745; height: 100%;"></div>
        </div>
    </div>
</div>

<?php if (!empty($advancedAnalytics)): ?>
<div class="babok-advanced-analytics" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; margin-bottom: 25px; color: white; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);">
    <h4 style="margin-top: 0; margin-bottom: 20px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-chart-line"></i>
        داشبورد تحلیلی پیشرفته پروژه
    </h4>
    
    <!-- KPIs اصلی -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <!-- شاخص سلامت پروژه -->
        <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">
                <?= $advancedAnalytics['kpis']['health_index'] ?>
            </div>
            <div style="font-size: 0.85rem; opacity: 0.9;">شاخص سلامت پروژه</div>
            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">
                <?php 
                    $health = $advancedAnalytics['kpis']['health_index'];
                    echo $health >= 80 ? '🟢 عالی' : ($health >= 60 ? '🟡 خوب' : '🔴 نیاز به توجه');
                ?>
            </div>
        </div>
        
        <!-- نرخ تکمیل -->
        <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">
                <?= $advancedAnalytics['kpis']['completion_rate'] ?>%
            </div>
            <div style="font-size: 0.85rem; opacity: 0.9;">نرخ تکمیل</div>
            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">
                <?= $advancedAnalytics['kpis']['completed_tasks'] ?> از <?= $advancedAnalytics['kpis']['total_tasks'] ?> وظیفه
            </div>
        </div>
        
        <!-- میانگین کیفیت -->
        <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">
                <?= $advancedAnalytics['kpis']['avg_quality'] ?>
            </div>
            <div style="font-size: 0.85rem; opacity: 0.9;">میانگین کیفیت</div>
            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">
                از ۱۰۰
            </div>
        </div>
        
        <!-- پوشش حوزه‌های دانشی -->
        <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 20px; border-radius: 10px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">
                <?= $advancedAnalytics['kpis']['ka_coverage'] ?>%
            </div>
            <div style="font-size: 0.85rem; opacity: 0.9;">پوشش حوزه‌های دانشی</div>
            <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">
                <?= count($advancedAnalytics['knowledge_area_progress']) ?> از ۶ حوزه
            </div>
        </div>
    </div>
    
    <!-- پیشرفت بر اساس حوزه دانشی -->
    <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 20px; border-radius: 10px;">
        <h5 style="margin-top: 0; margin-bottom: 15px; font-size: 1rem;">
            <i class="fas fa-sitemap"></i> پیشرفت بر اساس حوزه‌های دانشی
        </h5>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($advancedAnalytics['knowledge_area_progress'] as $ka): ?>
                <?php 
                    $kaProgress = $ka['total_tasks'] > 0 ? round(($ka['completed_tasks'] / $ka['total_tasks']) * 100) : 0;
                ?>
                <div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem;">
                        <span>
                            <strong><?= htmlspecialchars($ka['code']) ?></strong> - 
                            <?= htmlspecialchars($ka['name']) ?>
                        </span>
                        <span><?= $kaProgress ?>% (<?= $ka['completed_tasks'] ?>/<?= $ka['total_tasks'] ?>)</span>
                    </div>
                    <div style="width: 100%; background: rgba(255,255,255,0.2); border-radius: 99px; height: 8px; overflow: hidden;">
                        <div style="width: <?= $kaProgress ?>%; background: linear-gradient(90deg, #10b981, #34d399); height: 100%; border-radius: 99px; transition: width 0.5s ease;"></div>
                    </div>
                    <?php if ($ka['avg_quality'] > 0): ?>
                        <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 4px;">
                            میانگین کیفیت: <?= $ka['avg_quality'] ?>/100
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- داشبورد هوشمند کیفیت (بالای جدول وظایف) -->
<?php if (!empty($qualityStats) && $qualityStats['total_tasks'] > 0): ?>
<div class="babok-quality-dashboard" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <h4 style="margin-top: 0; margin-bottom: 20px; color: #1e293b; font-size: 1.1rem; border-bottom: 2px solid #6C3CE1; padding-bottom: 10px; display: inline-block;">
        📊 داشبورد هوشمند کیفیت نیازمندی‌ها
    </h4>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 20px;">
        <div style="background: #f8fafc; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0;">
            <div style="font-size: 1.8rem; font-weight: 800; color: #6C3CE1;"><?= number_format($qualityStats['avg_score'], 1) ?></div>
            <div style="font-size: 0.85rem; color: #64748b; margin-top: 5px;">میانگین کیفیت</div>
        </div>
        <div style="background: #f0fdf4; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #bbf7d0;">
            <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a;"><?= $qualityStats['excellent_count'] ?? 0 ?></div>
            <div style="font-size: 0.85rem; color: #15803d; margin-top: 5px;">عالی (≥80)</div>
        </div>
        <div style="background: #fffbeb; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #fde68a;">
            <div style="font-size: 1.8rem; font-weight: 800; color: #d97706;"><?= $qualityStats['good_count'] ?? 0 ?></div>
            <div style="font-size: 0.85rem; color: #b45309; margin-top: 5px;">قابل قبول (60-79)</div>
        </div>
        <div style="background: #fef2f2; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #fecaca;">
            <div style="font-size: 1.8rem; font-weight: 800; color: #dc2626;"><?= $qualityStats['needs_improvement_count'] ?? 0 ?></div>
            <div style="font-size: 0.85rem; color: #b91c1c; margin-top: 5px;">نیاز به بازنگری (<60)</div>
        </div>
    </div>

    <?php 
        $total = $qualityStats['total_tasks'];
        $excellent = $qualityStats['excellent_count'];
        $progressPercent = $total > 0 ? round(($excellent / $total) * 100) : 0;
    ?>
    <div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem; color: #475569;">
            <span>درصد نیازمندی‌های با کیفیت عالی</span>
            <span style="font-weight: bold;"><?= $progressPercent ?>%</span>
        </div>
        <div style="width: 100%; background-color: #e2e8f0; border-radius: 9999px; height: 10px; overflow: hidden;">
            <div style="width: <?= $progressPercent ?>%; background-color: #16a34a; height: 100%; border-radius: 9999px; transition: width 0.5s ease;"></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($traceabilitySuggestions)): ?>
<div class="babok-traceability-dashboard" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <h4 style="margin-top: 0; margin-bottom: 15px; color: #0369a1; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-link"></i>
        پیشنهادات هوشمند ردیابی نیازمندی‌ها (Traceability)
    </h4>
    <p style="font-size: 0.9rem; color: #0c4a6e; margin-bottom: 15px;">
        سیستم به صورت خودکار ارتباط بین خروجی وظایف تکمیل‌شده و ورودی وظایف شروع‌نشده را تحلیل کرده است:
    </p>
    
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($traceabilitySuggestions as $suggestion): ?>
            <?php 
                // اطمینان از وجود مقدار و جلوگیری از نمایش "نامشخص" خالی
                $artifacts = $suggestion['shared_artifacts'] ?? '';
                $displayArtifacts = (trim($artifacts) === '' || strtolower(trim($artifacts)) === 'نامشخص') 
                    ? '<span style="color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> تطابق دقیق یافت نشد (لطفاً به صورت دستی بررسی کنید)</span>' 
                    : '<i class="fas fa-file-alt"></i> ' . htmlspecialchars($artifacts);
            ?>
            <div style="background: #ffffff; border-left: 4px solid #0ea5e9; border-radius: 6px; padding: 15px; display: flex; align-items: flex-start; gap: 15px; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                <div style="font-size: 1.5rem; color: #0ea5e9;">🔗</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #0369a1; margin-bottom: 5px;">
                        <?= htmlspecialchars($suggestion['source_task_name']) ?> 
                        <span style="color: #94a3b8; margin: 0 8px;">➔</span> 
                        <?= htmlspecialchars($suggestion['target_task_name']) ?>
                    </div>
                    <div style="font-size: 0.85rem; color: #475569; margin-bottom: 8px;">
                        <?= htmlspecialchars($suggestion['recommendation']) ?>
                    </div>
                    <div style="display: inline-block; background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 99px; font-size: 0.75rem; font-weight: 500;">
                        مستندات مشترک: <?= $displayArtifacts ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($smartTechniques)): ?>
<div class="babok-smart-recommender" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
    <h4 style="margin-top: 0; margin-bottom: 15px; color: #0369a1; font-size: 1.1rem; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-lightbulb" style="color: #f59e0b;"></i>
        تکنیک‌های پیشنهادی هوشمند (بر اساس متدولوژی <?= \App\Software\Babok\Helpers\Utils::methodologyLabel($project['methodology'] ?? 'hybrid') ?>)
    </h4>
    
    <div style="display: flex; flex-direction: column; gap: 12px;">
        <?php foreach ($smartTechniques as $index => $tech): ?>
            <?php 
                // استخراج ایمن داده‌ها از آرایه تو در تو برای جلوگیری از خطای Undefined key
                $t = $tech['technique'] ?? [];
                $techId = $t['id'] ?? 0;
                $techName = $t['name'] ?? 'نامشخص';
                $techCategory = $t['category'] ?? 'عمومی';
                $techDesc = $t['description'] ?? '';
                $score = $tech['score'] ?? 0;
                $taskCount = $tech['task_count'] ?? 0;
            ?>
            <div style="background: #ffffff; border-left: 4px solid <?= $index === 0 ? '#f59e0b' : '#0ea5e9' ?>; border-radius: 8px; padding: 15px; display: flex; align-items: flex-start; gap: 15px; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(5px)'" onmouseout="this.style.transform='translateX(0)'">
                
                <!-- رتبه و امتیاز -->
                <div style="min-width: 50px; text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: <?= $index === 0 ? '#f59e0b' : '#0ea5e9' ?>;">
                        #<?= $index + 1 ?>
                    </div>
                    <div style="font-size: 0.75rem; color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; margin-top: 4px;">
                        امتیاز: <?= $score ?>
                    </div>
                </div>

                <!-- محتوا -->
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <strong style="color: #1e293b; font-size: 1rem;"><?= htmlspecialchars($techName) ?></strong>
                        <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">
                            <?= htmlspecialchars($techCategory) ?>
                        </span>
                        <?php if ($taskCount > 1): ?>
                            <span style="background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem;">
                                مرتبط با <?= $taskCount ?> وظیفه
                            </span>
                        <?php endif; ?>
                    </div>
                    <p style="margin: 0; font-size: 0.85rem; color: #475569; line-height: 1.5;">
                        <?= htmlspecialchars(mb_substr($techDesc, 0, 120)) ?>...
                    </p>
                </div>

                <!-- دکمه مشاهده -->
                <a href="?route=techniques_view&id=<?= $techId ?>" style="margin-right: 10px; padding: 8px 12px; background: #ffffff; border: 1px solid #cbd5e1; color: #0f172a; text-decoration: none; border-radius: 6px; font-size: 0.8rem; white-space: nowrap; align-self: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">
                    مشاهده جزئیات <i class="fas fa-arrow-left" style="font-size: 0.7rem;"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- لیست وظایف پروژه -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title">
            <i class="fas fa-tasks"></i> وظایف پروژه (<?= count($tasks) ?>)
        </div>
        <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-plus"></i> افزودن وظیفه
        </a>
    </div>
    
    <?php if (empty($tasks)): ?>
        <div class="text-muted text-center" style="padding: 30px 0;">
            <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3;"></i>
            <p>هنوز وظیفه‌ای به این پروژه اضافه نشده است.</p>
            <a href="?route=planning&id=<?= $project['id'] ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> افزودن وظیفه
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 12px; text-align: right;">کد</th>
                        <th style="padding: 12px; text-align: right;">نام وظیفه</th>
                        <th style="padding: 12px; text-align: right;">حوزه دانشی</th>
                        <th style="padding: 12px; text-align: right;">وضعیت</th>
                        <th style="padding: 12px; text-align: right;">امتیاز کیفیت</th>
                        <th style="padding: 12px; text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                    <tr style="border-bottom: 1px solid #dee2e6;">
                        <td style="padding: 12px;"><span class="badge badge-secondary"><?= htmlspecialchars($task['task_code']) ?></span></td>
                        <td style="padding: 12px;">
                            <a href="?route=tasks_view&id=<?= $task['task_id'] ?>" style="color: #6C3CE1; text-decoration: none; font-weight: 500;">
                                <?= htmlspecialchars($task['task_name']) ?>
                            </a>
                        </td>
                        <td style="padding: 12px;"><?= htmlspecialchars($task['knowledge_area_name']) ?></td>
                        <td style="padding: 12px;">
                            <span class="badge status-<?= str_replace('_', '-', $task['status']) ?>" style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">
                                <?= \App\Software\Babok\Helpers\Utils::statusLabel($task['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <?php 
                                $score = $task['quality_score'] ?? 0;
                                $color = $score >= 80 ? '#28a745' : ($score >= 60 ? '#ffc107' : ($score > 0 ? '#dc3545' : '#6c757d'));
                            ?>
                            <span style="font-weight: bold; color: <?= $color ?>;"><?= $score > 0 ? $score : '-' ?></span>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <!-- دکمه‌ای که مودال را باز می‌کند و داده‌ها را منتقل می‌کند -->
                            <button type="button" class="btn btn-sm btn-info open-task-modal" 
                                data-id="<?= $task['id'] ?>" 
                                data-notes="<?= htmlspecialchars($task['notes'] ?? '') ?>"
                                data-score="<?= $score ?>">
                                <i class="fas fa-edit"></i> یادداشت و کیفیت
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================== -->
<!-- Modal ویرایش یادداشت و کیفیت وظیفه -->
<!-- ========================================== -->
<div id="taskEditModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.2rem; color: #1e293b;"><i class="fas fa-edit"></i> ویرایش یادداشت و تحلیل کیفیت</h3>
            <button type="button" id="closeModalBtn" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <form id="taskQualityForm" style="padding: 20px;">
            <!-- فیلدهای مخفی حیاتی برای جاوااسکریپت و ارسال فرم -->
            <input type="hidden" name="project_task_id" id="modal_project_task_id">
            <input type="hidden" name="quality_score" id="modal_quality_score_input" value="0">
            <input type="hidden" name="status" value="in_progress">
            
            <div style="margin-bottom: 15px;">
                <label for="modal_project_task_notes" style="display: block; margin-bottom: 8px; font-weight: 600; color: #334155;">یادداشت‌ها / شرح نیازمندی:</label>
                <textarea id="modal_project_task_notes" name="notes" rows="5" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;" placeholder="نیازمندی را اینجا بنویسید (مثال: سیستم باید امکان صدور فاکتور را داشته باشد...)"></textarea>
            </div>

            <!-- باکس نمایش آنی نتیجه تحلیل -->
            <div id="quality-validation-box" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 20px; transition: all 0.3s ease;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-weight: bold; font-size: 0.9rem; color: #475569;">تحلیل هوشمند کیفیت (BABOK):</span>
                    <span id="quality-score-badge" class="badge rounded-pill" style="padding: 6px 12px; font-size: 0.85rem;">0</span>
                </div>
                <ul id="quality-issues" style="margin: 0 0 10px 0; padding-right: 20px; font-size: 0.85rem; color: #dc2626;"></ul>
                <div id="quality-suggestions" style="font-size: 0.85rem; color: #2563eb; font-weight: 500;"></div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e2e8f0; padding-top: 15px;">
                <button type="button" id="cancelModalBtn" class="btn btn-secondary" style="padding: 8px 16px; border: 1px solid #cbd5e1; background: white; border-radius: 6px; cursor: pointer;">انصراف</button>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px; background: #6C3CE1; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-save"></i> ذخیره تغییرات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. مدیریت Modal
    const modal = document.getElementById('taskEditModal');
    const openBtns = document.querySelectorAll('.open-task-modal');
    const closeBtns = [document.getElementById('closeModalBtn'), document.getElementById('cancelModalBtn')];
    
    // المان‌های داخل فرم (این‌ها همان‌هایی هستند که JS قبلی دنبالش می‌گشت)
    const textarea = document.getElementById('modal_project_task_notes');
    const hiddenScoreInput = document.getElementById('modal_quality_score_input');
    const taskIdInput = document.getElementById('modal_project_task_id');
    const validationBox = document.getElementById('quality-validation-box');
    const scoreBadge = document.getElementById('quality-score-badge');
    const issuesList = document.getElementById('quality-issues');
    const suggestionsDiv = document.getElementById('quality-suggestions');

    // باز کردن مودال و پر کردن داده‌ها
    openBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            taskIdInput.value = this.getAttribute('data-id');
            textarea.value = this.getAttribute('data-notes');
            hiddenScoreInput.value = this.getAttribute('data-score');
            
            // ریست کردن UI تحلیل
            validationBox.style.display = 'none';
            modal.style.display = 'flex';
            
            // اگر متنی از قبل وجود دارد، یک بار تحلیل را اجرا کن
            if (textarea.value.trim().length >= 15) {
                validateAndSaveRequirement(textarea.value.trim());
            }
        });
    });

    // بستن مودال
    closeBtns.forEach(btn => {
        if(btn) {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
    });

    // بستن با کلیک بیرون از مودال
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // 2. منطق تحلیل آنی (Debounce)
    let debounceTimer;
    textarea.addEventListener('input', function() {
        const text = this.value.trim();
        clearTimeout(debounceTimer);

        if (text.length < 15) {
            validationBox.style.display = 'none';
            hiddenScoreInput.value = 0;
            return;
        }

        debounceTimer = setTimeout(() => {
            validateAndSaveRequirement(text);
        }, 800);
    });

    function validateAndSaveRequirement(text) {
        const formData = new FormData();
        formData.append('text', text);
        formData.append('methodology', '<?= $project['methodology'] ?? 'hybrid' ?>');

        // نمایش حالت در حال بارگذاری
        scoreBadge.textContent = 'در حال تحلیل...';
        scoreBadge.style.backgroundColor = '#6c757d';
        scoreBadge.style.color = 'white';
        validationBox.style.display = 'block';

        fetch('?route=requirement_validate_quality_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) return;

            // به‌روزرسانی فیلد مخفی
            hiddenScoreInput.value = data.score;

            // به‌روزرسانی UI
            scoreBadge.textContent = data.score + '/100 (' + data.grade + ')';
            
            if (data.score >= 80) {
                validationBox.style.backgroundColor = '#f0fdf4';
                validationBox.style.borderColor = '#bbf7d0';
                scoreBadge.style.backgroundColor = '#22c55e';
            } else if (data.score >= 60) {
                validationBox.style.backgroundColor = '#fffbeb';
                validationBox.style.borderColor = '#fde68a';
                scoreBadge.style.backgroundColor = '#f59e0b';
                scoreBadge.style.color = '#000';
            } else {
                validationBox.style.backgroundColor = '#fef2f2';
                validationBox.style.borderColor = '#fecaca';
                scoreBadge.style.backgroundColor = '#ef4444';
                scoreBadge.style.color = 'white';
            }

            // نمایش مشکلات
            issuesList.innerHTML = '';
            if (data.issues && data.issues.length > 0) {
                data.issues.forEach(issue => {
                    const li = document.createElement('li');
                    li.textContent = '⚠️ ' + issue;
                    issuesList.appendChild(li);
                });
            }

            // نمایش پیشنهادات
            if (data.suggestions && data.suggestions.length > 0) {
                suggestionsDiv.innerHTML = '💡 پیشنهاد بهبود: ' + data.suggestions.join(' ');
            } else {
                suggestionsDiv.innerHTML = '✅ نیازمندی از کیفیت مطلوبی برخوردار است.';
            }
        })
        .catch(error => {
            console.error('خطا در تحلیل:', error);
            scoreBadge.textContent = 'خطا';
        });
    }

    // 3. ارسال فرم به سرور (AJAX)
    document.getElementById('taskQualityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
        submitBtn.disabled = true;

        const formData = new FormData(this);

        fetch('?route=projects_update_task_quality_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('یادداشت و امتیاز کیفیت با موفقیت ذخیره شد.');
                modal.style.display = 'none';
                location.reload(); // رفرش صفحه برای به‌روزرسانی داشبورد و جدول
            } else {
                alert('خطا در ذخیره‌سازی: ' + (data.error || 'مشکل نامشخص'));
            }
        })
        .catch(error => {
            console.error('خطا در ارسال فرم:', error);
            alert('خطای شبکه در ذخیره‌سازی.');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>