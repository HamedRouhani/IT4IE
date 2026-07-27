<?php
namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    
    public function getUnreadCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'unread'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch()['count'];
    }
    
    public function markAsRead($id)
    {
        return $this->update($id, ['status' => 'read']);
    }
    
    public function getUserMessages($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    
    public function getMessagesWithReplies()
    {
        $sql = "SELECT m.*, 
                       (SELECT COUNT(*) FROM messages WHERE parent_id = m.id) as reply_count,
                       (SELECT name FROM users WHERE id = m.user_id) as user_name
                FROM {$this->table} m
                WHERE m.parent_id IS NULL
                ORDER BY m.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getReplies($messageId)
    {
        $sql = "SELECT m.*, u.name as admin_name 
                FROM {$this->table} m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.parent_id = :parent_id 
                ORDER BY m.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['parent_id' => $messageId]);
        return $stmt->fetchAll();
    }
    
    public function addReply($messageId, $adminId, $content)
    {
        // دریافت پیام اصلی برای دریافت email و name
        $parentMessage = $this->find($messageId);
        if (!$parentMessage) {
            return false;
        }
        
        // ذخیره پاسخ
        $data = [
            'name' => 'مدیر سایت',
            'email' => 'admin@it4ie.ir',
            'subject' => 'پاسخ به: ' . $parentMessage['subject'],
            'message' => $content,
            'user_id' => $adminId,
            'parent_id' => $messageId,
            'status' => 'replied',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $result = $this->create($data);
        
        if ($result) {
            // به‌روزرسانی وضعیت پیام اصلی
            $this->update($messageId, ['status' => 'replied']);
        }
        
        return $result;
    }
}