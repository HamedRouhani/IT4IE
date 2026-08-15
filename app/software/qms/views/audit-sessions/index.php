<?php
$pageTitle = 'جلسات ممیزی';
$currentPage = 'auditsessions';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-clipboard-check" style="color: #6C3CE1;"></i>
            جلسات ممیزی
        </h1>
        <a href="?controller=auditplans&action=create" 
           style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> برنامه ممیزی جدید
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php
    // دریافت برنامه‌های ممیزی
    $plans = $this->db->query("
        SELECT ap.*, 
               (SELECT COUNT(*) FROM {$this->prefix}audit_plan_items WHERE audit_plan_id = ap.id) as session_count
        FROM {$this->prefix}audit_plans ap
        ORDER BY ap.created_at DESC
    ")->fetchAll();
    ?>

    <?php if (empty($plans)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <i class="fas fa-clipboard-list" style="font-size: 4rem; opacity: 0.3; color: #6C3CE1;"></i>
            <h4 style="margin: 20px 0 10px 0; color: #2D3748;">هیچ برنامه ممیزی ثبت نشده است</h4>
            <p style="color: #718096; margin-bottom: 20px;">برای شروع، اولین برنامه ممیزی خود را ایجاد کنید</p>
            <a href="?controller=auditplans&action=create" 
               style="display: inline-block; background: #6C3CE1; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-plus"></i> ایجاد برنامه ممیزی
            </a>
        </div>
    <?php else: ?>
        <!-- نمایش برنامه‌های ممیزی -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 30px;">
            <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                <i class="fas fa-folder-open" style="color: #6C3CE1;"></i>
                برنامه‌های ممیزی (<?= count($plans) ?>)
            </h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
                <?php foreach ($plans as $plan): ?>
                    <div style="border: 1px solid #E2E8F0; border-radius: 10px; padding: 15px; background: #F7FAFC; transition: all 0.2s;" 
                         onmouseover="this.style.borderColor='#6C3CE1'; this.style.boxShadow='0 4px 12px rgba(108,60,225,0.15)'" 
                         onmouseout="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <h4 style="margin: 0; color: #2D3748; font-size: 1.05rem; flex: 1;">
                                <?= qms_e($plan['title']) ?>
                            </h4>
                            <?php
                            $statusColors = [
                                'draft' => '#6B7280',
                                'scheduled' => '#3B82F6',
                                'in_progress' => '#F59E0B',
                                'completed' => '#10B981',
                                'cancelled' => '#EF4444'
                            ];
                            $statusLabels = [
                                'draft' => 'پیش‌نویس',
                                'scheduled' => 'زمان‌بندی شده',
                                'in_progress' => 'در حال انجام',
                                'completed' => 'تکمیل شده',
                                'cancelled' => 'لغو شده'
                            ];
                            $color = $statusColors[$plan['status']] ?? '#6B7280';
                            ?>
                            <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600;">
                                <?= $statusLabels[$plan['status']] ?? $plan['status'] ?>
                            </span>
                        </div>
                        
                        <div style="font-size: 0.85rem; color: #4A5568; margin-bottom: 10px;">
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-calendar" style="color: #6C3CE1; width: 16px;"></i>
                                <?= date('Y/m/d', strtotime($plan['start_date'])) ?> تا <?= date('Y/m/d', strtotime($plan['end_date'])) ?>
                            </div>
                            <div style="margin-bottom: 4px;">
                                <i class="fas fa-tasks" style="color: #6C3CE1; width: 16px;"></i>
                                <?= $plan['session_count'] ?> جلسه
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 8px; margin-top: 12px;">
                            <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" 
                               style="flex: 1; background: #6C3CE1; color: white; padding: 8px; border-radius: 6px; text-align: center; text-decoration: none; font-size: 0.85rem;">
                                <i class="fas fa-eye"></i> مشاهده
                            </a>
                            <a href="?controller=auditplans&action=edit&id=<?= $plan['id'] ?>" 
                               style="background: #F59E0B; color: white; padding: 8px 12px; border-radius: 6px; text-decoration: none;">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- نمایش جلسات -->
        <?php if (!empty($sessions)): ?>
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-clipboard-check" style="color: #6C3CE1;"></i>
                    جلسات ثبت شده
                </h3>
                
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                                <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">برنامه</th>
                                <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">واحد</th>
                                <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ</th>
                                <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                                <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                                <tr style="border-bottom: 1px solid #F0F0F0;">
                                    <td style="padding: 12px; color: #2D3748;"><?= qms_e($session['plan_title'] ?? '-') ?></td>
                                    <td style="padding: 12px; color: #4A5568;"><?= qms_e($session['department_name'] ?? '-') ?></td>
                                    <td style="padding: 12px; color: #4A5568;"><?= qms_e($session['actual_date'] ?? $session['audit_date'] ?? '-') ?></td>
                                    <td style="padding: 12px;">
                                        <?php
                                        $sessionStatusColors = [
                                            'not_started' => '#6B7280',
                                            'in_progress' => '#F59E0B',
                                            'completed' => '#10B981',
                                            'postponed' => '#EF4444'
                                        ];
                                        $sColor = $sessionStatusColors[$session['overall_status']] ?? '#6B7280';
                                        ?>
                                        <span style="background: <?= $sColor ?>20; color: <?= $sColor ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                            <?= qms_status_label($session['overall_status']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <a href="?controller=auditsessions&action=show&id=<?= $session['id'] ?>" 
                                           style="background: #6C3CE1; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                            <i class="fas fa-eye"></i> مشاهده
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>