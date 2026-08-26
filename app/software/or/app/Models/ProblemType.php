<?php
namespace App\Software\Or\Models;
use App\Software\Or\Core\Model;

class ProblemType extends Model
{
    protected $table = 'problem_types';
    public function getAll() { return $this->findAll([], 'id ASC'); }
    public function getWithDetails($id) { return $this->find($id); }
    public function getByCode($code)
    {
        return $this->queryOne("SELECT * FROM {$this->getTableName()} WHERE code = ?", [$code]);
    }
}