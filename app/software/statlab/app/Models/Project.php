<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Project extends Model
{
    protected $table = 'projects';

    public function deleteOwned(int $projectId, int $userId): bool
    {
        if (!$this->db) return false;

        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->getTableName()}` WHERE id = :id AND user_id = :user_id"
        );
        $stmt->execute(['id' => $projectId, 'user_id' => $userId]);

        return $stmt->rowCount() === 1;
    }

    public function countByUser(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }

    public function countByUserAndStatus(int $userId, string $status): int
    {
        return $this->count(['user_id' => $userId, 'status' => $status]);
    }

    public function getRecentByUser(int $userId, int $limit = 5): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    /** لیست سبک پروژه‌ها برای select ها */
    public function getListForUser(int $userId, int $limit = 50): array
    {
        return $this->query(
            "SELECT id, name, category_code, status, updated_at
             FROM `{$this->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }
}
