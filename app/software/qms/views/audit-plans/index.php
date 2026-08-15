<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-clipboard-list" style="color: #6C3CE1;"></i>
            برنامه‌های ممیزی
        </h1>
        <!-- ✅ اصلاح شده: استفاده از لینک نسبی ماژولار -->
        <a href="?controller=auditplans&action=create" class="btn" style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> برنامه جدید
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($plans)): ?>
            <div class="text-center py-5" style="padding: 50px 20px; color: #718096;">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 15px;"></i>
                <h4 class="text-muted" style="margin: 0 0 10px 0;">هیچ برنامه ممیزی ثبت نشده است</h4>
                <!-- ✅ اصلاح شده -->
                <a href="?controller=auditplans&action=create" class="btn" style="background: #6C3CE1; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 15px;">
                    <i class="fas fa-plus"></i> ایجاد اولین برنامه
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">نوع</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">سرممیز</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ شروع</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ پایان</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plans as $plan): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;">
                                    <!-- ✅ اصلاح شده -->
                                    <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" style="text-decoration: none; color: #2D3748; font-weight: 600;">
                                        <?= htmlspecialchars($plan['title']) ?>
                                    </a>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background: <?= $plan['audit_type'] === 'internal' ? '#DBEAFE' : '#FEF3C7' ?>; color: <?= $plan['audit_type'] === 'internal' ? '#1E40AF' : '#92400E' ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= $plan['audit_type'] === 'internal' ? 'داخلی' : 'خارجی' ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #4A5568;"><?= htmlspecialchars($plan['lead_auditor_name'] ?? '-') ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= date('Y/m/d', strtotime($plan['start_date'])) ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= date('Y/m/d', strtotime($plan['end_date'])) ?></td>
                                <td style="padding: 12px;">
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
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= $statusLabels[$plan['status']] ?? $plan['status'] ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: inline-flex; gap: 5px;">
                                        <!-- ✅ اصلاح شده: لینک مشاهده -->
                                        <a href="?controller=auditplans&action=show&id=<?= $plan['id'] ?>" class="btn-sm" style="background: #3B82F6; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <?php if (($_SESSION['user_role'] ?? '') === 'admin' || $plan['user_id'] == ($_SESSION['user_id'] ?? 0)): ?>
                                            <!-- ✅ اصلاح شده: لینک ویرایش -->
                                            <a href="?controller=auditplans&action=edit&id=<?= $plan['id'] ?>" class="btn-sm" style="background: #F59E0B; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;" title="ویرایش">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                            <!-- ✅ اصلاح شده: فرم حذف -->
                                            <form method="POST" action="?controller=auditplans&action=delete&id=<?= $plan['id'] ?>" 
                                                  onsubmit="return confirm('آیا از حذف این برنامه مطمئن هستید؟');" 
                                                  style="display: inline;">
                                                <button type="submit" class="btn-sm" style="background: #EF4444; color: white; padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>