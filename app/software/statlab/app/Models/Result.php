<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Result extends Model
{
    protected $table = 'results';

    public function getByProject(int $projectId): array
    {
        return $this->findAll(['project_id' => $projectId], 'id DESC');
    }

    public function getLatestByProject(int $projectId, int $limit = 5): array
    {
        return $this->findAll(['project_id' => $projectId], 'id DESC', (string)$limit);
    }
}