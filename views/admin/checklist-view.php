<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📄 جزئیات چک‌لیست #<?php echo $submission['id']; ?></h1>
            <a href="/admin/checklist" class="btn-register" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت به لیست
            </a>
        </div>

        <!-- اطلاعات کاربر -->
        <div class="admin-form" style="margin-bottom: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-user" style="color: var(--primary);"></i> اطلاعات کاربر
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">نام</label>
                    <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($submission['name']); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">ایمیل</label>
                    <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($submission['email']); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">تلفن</label>
                    <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($submission['phone'] ?? '-'); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">شرکت/پروژه</label>
                    <div style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($submission['company'] ?? '-'); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">تاریخ ارسال</label>
                    <div style="font-weight: 600; color: var(--dark);"><?php echo jdate($submission['created_at']); ?></div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">آدرس IP</label>
                    <div style="font-weight: 600; color: var(--dark); font-size: 0.85rem;"><?php echo htmlspecialchars($submission['ip_address'] ?? '-'); ?></div>
                </div>
            </div>
        </div>

        <!-- نتیجه کلی -->
        <?php
        $riskMap = [
            'critical' => ['label' => 'بحرانی', 'bg' => '#FEE2E2', 'color' => '#DC2626'],
            'high'     => ['label' => 'بالا', 'bg' => '#FEF3C7', 'color' => '#D97706'],
            'medium'   => ['label' => 'متوسط', 'bg' => 'rgba(108, 60, 225, 0.1)', 'color' => '#6c3ce1'],
            'low'      => ['label' => 'پایین', 'bg' => '#D1FAE5', 'color' => '#16A34A']
        ];
        $risk = $riskMap[$submission['risk_level']] ?? $riskMap['medium'];
        $percentage = round(($submission['total_score'] / max(1, $submission['max_score'])) * 100);
        ?>
        <div class="admin-form" style="margin-bottom: 20px; border-right: 4px solid <?php echo $risk['color']; ?>;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-chart-pie" style="color: var(--primary);"></i> نتیجه ارزیابی
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">امتیاز کسب شده</label>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--dark);">
                        <?php echo $submission['total_score']; ?> / <?php echo $submission['max_score']; ?>
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">درصد ریسک</label>
                    <div style="font-size: 1.5rem; font-weight: 800; color: <?php echo $risk['color']; ?>;">
                        <?php echo $percentage; ?>٪
                    </div>
                </div>
                <div>
                    <label style="font-size: 0.8rem; color: var(--gray);">سطح ریسک</label>
                    <div style="margin-top: 4px;">
                        <span class="status-badge" style="background: <?php echo $risk['bg']; ?>; color: <?php echo $risk['color']; ?>; font-size: 0.85rem; padding: 6px 16px;">
                            <?php echo $risk['label']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- پاسخ‌ها به تفکیک سوال -->
        <div class="admin-table" style="margin-bottom: 20px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid rgba(108, 60, 225, 0.04);">
                <h3 style="font-size: 1rem; font-weight: 700; margin: 0; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-list-check" style="color: var(--primary);"></i> پاسخ‌های کاربر
                </h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 70%;">سوال</th>
                        <th style="width: 30%; text-align: center;">پاسخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $answerMap = [
                        0 => ['label' => 'خیر، اصلاً (مطلوب)', 'bg' => '#D1FAE5', 'color' => '#16A34A'],
                        1 => ['label' => 'به ندرت', 'bg' => 'rgba(108, 60, 225, 0.1)', 'color' => '#6c3ce1'],
                        2 => ['label' => 'تا حدودی', 'bg' => '#FEF3C7', 'color' => '#D97706'],
                        3 => ['label' => 'بله، به‌شدت (ریسک بالا)', 'bg' => '#FEE2E2', 'color' => '#DC2626']
                    ];
                    ?>
                    <?php foreach ($questions as $q): 
                        $answer = $submission['answers_decoded'][$q['id']] ?? 0;
                        $ansStyle = $answerMap[$answer] ?? $answerMap[0];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($q['question_text']); ?></td>
                            <td style="text-align: center;">
                                <span class="status-badge" style="background: <?php echo $ansStyle['bg']; ?>; color: <?php echo $ansStyle['color']; ?>;">
                                    <?php echo $ansStyle['label']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- توصیه‌های تولید شده -->
        <div class="admin-form" style="margin-bottom: 20px;">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-lightbulb" style="color: var(--primary);"></i> توصیه‌های ارائه شده به کاربر
            </h3>
            <?php if (!empty($submission['recommendations_decoded'])): ?>
                <?php foreach ($submission['recommendations_decoded'] as $cat => $rec): ?>
                    <div style="background: var(--gray-lighter); border-radius: var(--radius); padding: 16px; margin-bottom: 12px;">
                        <div style="font-weight: 700; color: var(--dark); margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                            <span>
                                <i class="fas <?php echo $rec['icon'] ?? 'fa-circle'; ?>" style="color: var(--primary); margin-left: 6px;"></i>
                                <?php echo htmlspecialchars($rec['title']); ?>
                            </span>
                            <span class="status-badge" style="background: rgba(108, 60, 225, 0.1); color: #6c3ce1; font-size: 0.75rem;">
                                <?php echo $rec['percentage'] ?? 0; ?>٪ ریسک
                            </span>
                        </div>
                        <ul style="list-style: none; padding: 0; margin: 8px 0 0;">
                            <?php foreach (($rec['items'] ?? []) as $item): ?>
                                <li style="padding: 4px 0; color: var(--gray-dark); font-size: 0.85rem; display: flex; align-items: flex-start; gap: 8px;">
                                    <i class="fas fa-check" style="color: #16A34A; margin-top: 4px;"></i>
                                    <span><?php echo htmlspecialchars($item); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--gray); text-align: center; padding: 20px;">توصیه‌ای ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        <!-- فرم به‌روزرسانی وضعیت -->
        <div class="admin-form">
            <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 16px; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-edit" style="color: var(--primary);"></i> مدیریت وضعیت پیگیری
            </h3>
            <form method="POST" action="/admin/checklist/update-status">
                <input type="hidden" name="id" value="<?php echo $submission['id']; ?>">
                <div style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 16px; align-items: end;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>وضعیت</label>
                        <select name="status" style="width: 100%;">
                            <?php 
                            $statusOptions = ['new' => 'جدید', 'contacted' => 'تماس گرفته شده', 'converted' => 'تبدیل شده', 'archived' => 'بایگانی'];
                            foreach ($statusOptions as $key => $label): 
                            ?>
                                <option value="<?php echo $key; ?>" <?php echo ($submission['status'] ?? 'new') === $key ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>یادداشت ادمین</label>
                        <input type="text" name="notes" value="<?php echo htmlspecialchars($submission['admin_notes'] ?? ''); ?>" placeholder="مثال: تماس گرفته شد، جلسه هفته آینده">
                    </div>
                    <div>
                        <button type="submit" class="btn-admin-submit">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>