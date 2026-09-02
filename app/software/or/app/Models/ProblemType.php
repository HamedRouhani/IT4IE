<?php

namespace App\Software\Or\Models;

use App\Software\Or\Core\Model;
use PDO; // ✅ اصلاح خطا: وارد کردن کلاس PDO از فضای نام سراسری (Global)

class ProblemType extends Model
{
    // نام جدول بدون پیشوند "or_"، زیرا کلاس پایه Model به صورت خودکار پیشوند را مدیریت می‌کند.
    protected $table = 'problem_types';

    /**
     * دریافت تمام انواع مسائل فعال
     */
    public function getAll()
    {
        // ✅ اصلاح: استفاده از getTableName() برای اعمال خودکار پیشوند "or_"
        $sql = "SELECT * FROM {$this->getTableName()} ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * یافتن یک نوع مسئله بر اساس ID
     */
    public function find($id)
    {
        // ✅ اصلاح: یکپارچه‌سازی نام جدول با سایر متدها
        return $this->db->fetchOne(
            "SELECT * FROM {$this->getTableName()} WHERE id = ?",
            [$id]
        );
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