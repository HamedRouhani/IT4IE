<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Dataset extends Model
{
    protected $table = 'datasets';

    public function getByProject(int $projectId): array
    {
        return $this->findAll(['project_id' => $projectId], 'id DESC');
    }
}