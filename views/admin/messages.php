<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>✉️ مدیریت پیام‌ها</h1>
        </div>
        
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>فرستنده</th>
                        <th>موضوع</th>
                        <th>پیام</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--gray);">
                                هیچ پیامی وجود ندارد.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td><?php echo $message['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($message['name']); ?></strong>
                                    <br>
                                    <small style="color: #94a3b8;"><?php echo htmlspecialchars($message['email']); ?></small>
                                    <?php if ($message['phone']): ?>
                                        <br><small style="color: #94a3b8;"><?php echo htmlspecialchars($message['phone']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($message['subject']); ?></td>
                                <td>
                                    <div style="max-width: 200px; max-height: 60px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo nl2br(htmlspecialchars(substr($message['message'], 0, 100))); ?>
                                        <?php if (strlen($message['message']) > 100): ?>
                                            ...
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($message['reply_count'] > 0): ?>
                                        <span style="color: #22c55e; font-size: 12px;">
                                            <i class="fas fa-reply"></i> <?php echo $message['reply_count']; ?> پاسخ
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $message['status']; ?>">
                                        <?php 
                                        $statusLabels = [
                                            'unread' => 'خوانده نشده',
                                            'read' => 'خوانده شده',
                                            'replied' => 'پاسخ داده شده',
                                            'archived' => 'بایگانی'
                                        ];
                                        echo $statusLabels[$message['status']] ?? $message['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?php echo jdate($message['created_at']); ?></td>
                                <td class="actions">
                                    <!-- دکمه مشاهده و پاسخ -->
                                    <button class="btn-edit" onclick="showReplyForm(<?php echo $message['id']; ?>)">
                                        <i class="fas fa-reply"></i> پاسخ
                                    </button>
                                    
                                    <!-- فرم تغییر وضعیت -->
                                    <form method="POST" action="/admin/messages" style="display: inline;">
                                        <input type="hidden" name="action" value="change_status">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" style="border-radius: 6px; padding: 4px 8px; border: 1px solid var(--gray-light); background: white;">
                                            <option value="unread" <?php echo $message['status'] == 'unread' ? 'selected' : ''; ?>>خوانده نشده</option>
                                            <option value="read" <?php echo $message['status'] == 'read' ? 'selected' : ''; ?>>خوانده شده</option>
                                            <option value="replied" <?php echo $message['status'] == 'replied' ? 'selected' : ''; ?>>پاسخ داده شده</option>
                                            <option value="archived" <?php echo $message['status'] == 'archived' ? 'selected' : ''; ?>>بایگانی</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            
                            <!-- فرم پاسخ (مخفی) -->
                            <tr id="reply-form-<?php echo $message['id']; ?>" style="display: none; background: #f8fafc;">
                                <td colspan="7">
                                    <div style="padding: 16px;">
                                        <h4 style="margin-bottom: 8px;">✉️ پاسخ به: <?php echo htmlspecialchars($message['subject']); ?></h4>
                                        <div style="background: white; padding: 12px; border-radius: 8px; margin-bottom: 12px; border-right: 3px solid #2563eb;">
                                            <strong><?php echo htmlspecialchars($message['name']); ?>:</strong>
                                            <p style="margin: 4px 0;"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                        </div>
                                        
                                        <!-- نمایش پاسخ‌های قبلی -->
                                        <?php 
                                        $replies = (new \App\Models\Message())->getReplies($message['id']);
                                        if (!empty($replies)): 
                                        ?>
                                            <div style="margin: 12px 0;">
                                                <h5 style="color: #475569;">پاسخ‌های قبلی:</h5>
                                                <?php foreach ($replies as $reply): ?>
                                                    <div style="background: #f1f5f9; padding: 10px; border-radius: 6px; margin-bottom: 8px; border-right: 3px solid #22c55e;">
                                                        <strong>مدیر:</strong>
                                                        <p style="margin: 4px 0;"><?php echo nl2br(htmlspecialchars($reply['message'])); ?></p>
                                                        <small style="color: #94a3b8;"><?php echo jdate($reply['created_at']); ?></small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <form method="POST" action="/admin/messages">
                                            <input type="hidden" name="action" value="reply">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <div class="form-group" style="margin-bottom: 8px;">
                                                <textarea name="reply_content" rows="4" placeholder="متن پاسخ خود را وارد کنید..." style="width: 100%; padding: 10px; border: 2px solid #e2e8f0; border-radius: 8px; font-family: 'Vazirmatn', sans-serif; resize: vertical;"></textarea>
                                            </div>
                                            <div style="display: flex; gap: 8px;">
                                                <button type="submit" class="btn-admin-submit" style="padding: 8px 20px;">
                                                    <i class="fas fa-paper-plane"></i> ارسال پاسخ
                                                </button>
                                                <button type="button" onclick="hideReplyForm(<?php echo $message['id']; ?>)" style="padding: 8px 20px; background: #e2e8f0; border: none; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn', sans-serif;">
                                                    بستن
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showReplyForm(id) {
    // مخفی کردن همه فرم‌های پاسخ
    document.querySelectorAll('[id^="reply-form-"]').forEach(function(el) {
        el.style.display = 'none';
    });
    // نمایش فرم مربوطه
    document.getElementById('reply-form-' + id).style.display = 'table-row';
    // اسکرول به فرم
    document.getElementById('reply-form-' + id).scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideReplyForm(id) {
    document.getElementById('reply-form-' + id).style.display = 'none';
}
</script>