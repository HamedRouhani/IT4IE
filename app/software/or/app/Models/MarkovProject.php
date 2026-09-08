<?php
namespace App\Software\Or\Models;

use App\Software\Or\Core\Model;

class MarkovProject extends Model
{
    protected $table = 'markov_projects';

    public function getByUser(int $userId, int $limit = 20): array
    {
        return $this->query(
            "SELECT * FROM `{$this->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT " . (int)$limit,
            ['uid' => $userId]
        );
    }

    public function createMarkov(array $data): int
    {
        return (int)$this->create([
            'user_id'              => $data['user_id'],
            'name'                 => $data['name'],
            'description'          => $data['description'] ?? null,
            'states_json'          => $data['states_json'],
            'transition_matrix_json'=> $data['transition_matrix_json'],
            'initial_state_json'   => $data['initial_state_json'],
            'steps'                => $data['steps'],
            'result_json'          => $data['result_json'],
            'status'               => 'solved',
        ]);
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