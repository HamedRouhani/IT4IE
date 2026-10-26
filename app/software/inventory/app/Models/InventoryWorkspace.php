<?php
namespace App\Software\Inventory\Models;

use App\Core\Database;

final class InventoryWorkspace
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getOrCreateForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "INSERT INTO inv_workspaces (user_id, name) VALUES (?, 'انبار من')
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)"
        );
        $stmt->execute([$userId]);
        $stmt = $this->db->prepare('SELECT id, user_id, name FROM inv_workspaces WHERE user_id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $workspace = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$workspace) throw new \RuntimeException('فضای کاری موجودی کاربر پیدا نشد.');
        return $workspace;
    }
}
