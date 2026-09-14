<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📋 مدیریت چک‌لیست‌های ارزیابی ریسک</h1>
            <span>نمایش و پیگیری نتایج ارزیابی کاربران</span>
        </div>

        <?php if (!empty($filterChecklist)): ?>
        <div class="admin-form" style="margin-bottom: 16px; background: rgba(108, 60, 225, 0.06); border: 1px solid rgba(108, 60, 225, 0.15);">
            <i class="fas fa-filter" style="color: var(--primary); margin-left: 6px;"></i>
            نمایش نتایج فیلترشده برای: <strong><?= htmlspecialchars($filterChecklist['title']) ?></strong>
            <a href="/admin/checklist" style="color: var(--primary); font-weight: 700; margin-right: 12px;">حذف فیلتر</a>
        </div>
        <?php endif; ?>
        
        <div class="admin-stats">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-list"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['total'] ?? 0; ?></h3>
                    <p>کل ارسال‌ها</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['critical'] ?? 0; ?></h3>
                    <p>وضعیت بحرانی</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-exclamation-circle"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['high'] ?? 0; ?></h3>
                    <p>ریسک بالا</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-inbox"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['new_count'] ?? 0; ?></h3>
                    <p>جدید (پیگیری نشده)</p>
                </div>
            </div>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام کاربر</th>
                        <th>ایمیل</th>
                        <th>شرکت/پروژه</th>
                        <th>امتیاز</th>
                        <th>سطح ریسک</th>
                        <th>وضعیت پیگیری</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($submissions)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--gray); padding: 30px;">
                                <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px;"></i>
                                هنوز چک‌لیستی ثبت نشده است.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($submissions as $sub): 
                            // تعریف دقیق رنگ‌ها بر اساس پالت رنگی 9-admin.css بدون تداخل با کلاس‌های وضعیت
                            $riskMap = [
                                'critical' => ['label' => 'بحرانی', 'bg' => '#FEE2E2', 'color' => '#DC2626', 'icon' => 'fa-exclamation-triangle'],
                                'high'     => ['label' => 'بالا', 'bg' => '#FEF3C7', 'color' => '#D97706', 'icon' => 'fa-exclamation-circle'],
                                'medium'   => ['label' => 'متوسط', 'bg' => 'rgba(108, 60, 225, 0.1)', 'color' => '#6c3ce1', 'icon' => 'fa-exclamation'],
                                'low'      => ['label' => 'پایین', 'bg' => '#D1FAE5', 'color' => '#16A34A', 'icon' => 'fa-check-circle']
                            ];
                            $risk = $riskMap[$sub['risk_level']] ?? $riskMap['medium'];
                            
                            $statusMap = [
                                'new'       => ['label' => 'جدید', 'bg' => 'rgba(108, 60, 225, 0.08)', 'color' => '#6c3ce1'],
                                'contacted' => ['label' => 'تماس گرفته شده', 'bg' => '#FEF3C7', 'color' => '#D97706'],
                                'converted' => ['label' => 'تبدیل شده', 'bg' => '#D1FAE5', 'color' => '#16A34A'],
                                'archived'  => ['label' => 'بایگانی', 'bg' => 'var(--gray-light)', 'color' => 'var(--gray-dark)']
                            ];
                            $status = $statusMap[$sub['status']] ?? $statusMap['new'];
                            
                            $percentage = round(($sub['total_score'] / max(1, $sub['max_score'])) * 100);
                        ?>
                            <tr>
                                <td><?php echo $sub['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($sub['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($sub['email']); ?></td>
                                <td><?php echo htmlspecialchars($sub['company'] ?? '-'); ?></td>
                                <td>
                                    <strong><?php echo $sub['total_score']; ?>/<?php echo $sub['max_score']; ?></strong>
                                    <br><small style="color: var(--gray);">(<?php echo $percentage; ?>٪)</small>
                                </td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $risk['bg']; ?>; color: <?php echo $risk['color']; ?>;">
                                        <i class="fas <?php echo $risk['icon']; ?>"></i> <?php echo $risk['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge" style="background: <?php echo $status['bg']; ?>; color: <?php echo $status['color']; ?>;">
                                        <?php echo $status['label']; ?>
                                    </span>
                                </td>
                                <td><?php echo jdate($sub['created_at']); ?></td>
                                <td class="actions">
                                    <a href="/admin/checklist/view/<?= $sub['id'] ?>" class="btn-view" title="مشاهده جزئیات و پاسخ‌ها">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/checklist/result/<?= $sub['id'] ?>" class="btn-result" title="مشاهده صفحه نتیجه">
                                        <i class="fas fa-poll"></i>
                                    </a>
                                    <a href="mailto:<?= htmlspecialchars($sub['email']) ?>" class="btn-edit" title="ارسال ایمیل">
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>