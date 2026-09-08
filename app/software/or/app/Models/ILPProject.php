<?php
namespace App\Software\Or\Models;

use App\Software\Or\Core\Model;

class ILPProject extends Model
{
    protected $table = 'ilp_projects';

    /**
     * دریافت همه پروژه‌های کاربر (شامل draft و solved)
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    /**
     * فقط پروژه‌های حل‌شده
     */
    public function getSolvedByUser(int $userId, int $limit = 50): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE user_id = :uid AND status = 'solved' ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function createILP(array $data): int
    {
        return (int)$this->create([
            'user_id'             => $data['user_id'],
            'name'                => $data['name'],
            'description'         => $data['description'] ?? null,
            'objective_coeffs'    => $data['objective_coeffs'],
            'constraint_matrix'   => $data['constraint_matrix'],
            'rhs_values'          => $data['rhs_values'],
            'constraints_types'   => $data['constraints_types'],
            'integer_vars'        => $data['integer_vars'],
            'sense'               => $data['sense'],
            'result_json'         => $data['result_json'],
            'status'              => 'solved',
        ]);
    }

    /**
     * به‌روزرسانی پروژه
     */
    public function updateILP(int $id, int $userId, array $data): bool
    {
        return $this->execute(
            "UPDATE `{$this->getTableName()}` SET 
                name = :name,
                description = :description,
                objective_coeffs = :objective_coeffs,
                constraint_matrix = :constraint_matrix,
                rhs_values = :rhs_values,
                constraints_types = :constraints_types,
                integer_vars = :integer_vars,
                sense = :sense,
                result_json = :result_json,
                status = 'solved',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND user_id = :uid",
            [
                'id'                => $id,
                'uid'               => $userId,
                'name'              => $data['name'],
                'description'       => $data['description'] ?? null,
                'objective_coeffs'  => $data['objective_coeffs'],
                'constraint_matrix' => $data['constraint_matrix'],
                'rhs_values'        => $data['rhs_values'],
                'constraints_types' => $data['constraints_types'],
                'integer_vars'      => $data['integer_vars'],
                'sense'             => $data['sense'],
                'result_json'       => $data['result_json'],
            ]
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

    public function deleteOwned(int $id, int $userId): bool
    {
        return $this->execute(
            "DELETE FROM `{$this->getTableName()}` WHERE id = :id AND user_id = :uid",
            ['id' => $id, 'uid' => $userId]
        ) > 0;
    }
}