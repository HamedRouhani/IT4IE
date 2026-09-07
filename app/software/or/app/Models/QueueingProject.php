<?php
namespace App\Software\Or\Models;

use App\Software\Or\Core\Model;

class QueueingProject extends Model
{
    // ✅ فقط نام جدول بدون پیشوند - Model خودش or_ را اضافه می‌کند
    protected $table = 'queueing_projects';

    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` 
             WHERE user_id = :uid 
             ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function createQueueing(array $data): int
    {
        return (int)$this->create([
            'user_id'       => $data['user_id'],
            'name'          => $data['name'],
            'description'   => $data['description'] ?? null,
            'model_code'    => $data['model_code'],
            'lambda'        => $data['lambda'],
            'mu'            => $data['mu'],
            'servers'       => $data['servers'],
            'capacity'      => $data['capacity'],
            'service_std'   => $data['service_std'],
            'status'        => 'solved',
            'result_json'   => $data['result_json'],
        ]);
    }

    public function deleteOwned(int $id, int $userId): bool
    {
        return $this->execute(
            "DELETE FROM `{$this->getTableName()}` WHERE id = :id AND user_id = :uid",
            ['id' => $id, 'uid' => $userId]
        ) > 0;
    }

    public function findOwned(int $id, int $userId): ?array
    {
        $rows = $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE id = :id AND user_id = :uid",
            ['id' => $id, 'uid' => $userId]
        );
        return $rows[0] ?? null;
    }

    public function updateQueueing(int $id, array $data): bool
    {
        return (bool)$this->update($id, [
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'model_code'  => $data['model_code'],
            'lambda'      => $data['lambda'],
            'mu'          => $data['mu'],
            'servers'     => $data['servers'],
            'capacity'    => $data['capacity'],
            'service_std' => $data['service_std'],
            'status'      => $data['status'] ?? 'solved',
            'result_json' => $data['result_json'],
        ]);
    }
}