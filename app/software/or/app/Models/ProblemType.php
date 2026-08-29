<?php
namespace App\Software\Or\Models;
use App\Software\Or\Core\Model;

class ProblemType extends Model
{
    // نام جدول بدون پیشوند "or_"، زیرا کلاس پایه Model به صورت خودکار پیشوند را مدیریت می‌کند.
    protected $table = 'problem_types';

    /**
     * دریافت تمام انواع مسئله
     */
    public function getAll() 
    { 
        return $this->findAll([], 'id ASC'); 
    }

    /**
     * دریافت نوع مسئله بر اساس کد
     */
    public function getByCode($code)
    {
        return $this->queryOne("SELECT * FROM {$this->getTableName()} WHERE code = ?", [$code]);
    }

    // ✅ متد count() از کلاس پایه Model به ارث برده می‌شود.
    // نیازی به تعریف مجدد آن نیست. فراخوانی $this->problemTypeModel->count() به درستی کار خواهد کرد.
}