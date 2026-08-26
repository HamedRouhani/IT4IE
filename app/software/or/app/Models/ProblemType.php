<?php
namespace App\Software\Or\Models;
use App\Software\Or\Core\Model;

class ProblemType extends Model
{
    // اصلاح: نام جدول باید بدون پیشوند "or_" باشد، 
    // زیرا کلاس پایه Model به صورت خودکار پیشوند را اضافه می‌کند.
    protected $table = 'problem_types';

    public function getAll() 
    { 
        return $this->findAll([], 'id ASC'); 
    }

    public function getByCode($code)
    {
        // استفاده از متد getTableName() که مدیریت پیشوند را به درستی انجام می‌دهد
        return $this->queryOne("SELECT * FROM {$this->getTableName()} WHERE code = ?", [$code]);
    }
}