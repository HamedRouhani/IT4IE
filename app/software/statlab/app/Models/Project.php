<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Project extends Model
{
    protected $table = 'projects';

    public function getRecentByUser(int $userId, int $limit = 5): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function countByUser(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }

    public function countByUserAndStatus(int $userId, string $status): int
    {
        return $this->count(['user_id' => $userId, 'status' => $status]);
    }
}