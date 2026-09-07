<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Result extends Model
{
    protected $table = 'results';

    public function getByProject(int $projectId): array
    {
        return $this->query(
            "SELECT r.*, ds.name AS dataset_name
             FROM `{$this->getTableName()}` r
             LEFT JOIN `{$this->tablePrefix}datasets` ds ON r.dataset_id = ds.id
             WHERE r.project_id = :pid ORDER BY r.id ASC",
            ['pid' => $projectId]
        );
    }

    public function getLatestByProject(int $projectId, int $limit = 10): array
    {
        return $this->findAll(['project_id' => $projectId], 'id DESC', (string)$limit);
    }
}