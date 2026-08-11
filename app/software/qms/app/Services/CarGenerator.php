<?php

namespace App\Software\Qms\Services;

/**
 * سرویس تولید خودکار CAR از عدم انطباق
 */
class CarGenerator
{
    private $db;
    private $prefix = 'qms_';

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * تولید CAR خودکار از یک NC
     */
    public function generateFromNc($ncId, $userId)
    {
        // دریافت اطلاعات NC
        $stmt = $this->db->prepare("
            SELECT nc.*, c.clause_number, c.title_fa as clause_title
            FROM {$this->prefix}nonconformities nc
            LEFT JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            WHERE nc.id = ?
        ");
        $stmt->execute([$ncId]);
        $nc = $stmt->fetch();

        if (!$nc) {
            return ['success' => false, 'message' => 'عدم انطباق یافت نشد.'];
        }

        // بررسی وجود CAR قبلی
        $stmt = $this->db->prepare("SELECT id FROM {$this->prefix}car_forms WHERE nc_id = ?");
        $stmt->execute([$ncId]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'برای این عدم انطباق قبلاً CAR صادر شده است.'];
        }

        // تولید شماره CAR
        $year = date('Y');
        $stmt = $this->db->prepare("
            SELECT car_number FROM {$this->prefix}car_forms 
            WHERE car_number LIKE ? 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute(["CAR-{$year}-%"]);
        $last = $stmt->fetch();
        $newNum = $last ? (int)substr($last['car_number'], -3) + 1 : 1;
        $carNumber = sprintf('CAR-%s-%03d', $year, $newNum);

        // پیشنهاد اقدام اصلاحی بر اساس شدت
        $proposedAction = $this->suggestCorrectiveAction($nc['severity'], $nc['title']);

        // ایجاد CAR
        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}car_forms 
            (nc_id, car_number, title, nc_description, nc_clause_reference, 
             proposed_action, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'draft', ?, NOW())
        ");
        $stmt->execute([
            $ncId,
            $carNumber,
            'CAR برای: ' . $nc['title'],
            $nc['description'],
            $nc['clause_number'] . ' - ' . ($nc['clause_title'] ?? ''),
            $proposedAction,
            $userId
        ]);

        $carId = $this->db->lastInsertId();

        // به‌روزرسانی وضعیت NC
        $this->db->prepare("
            UPDATE {$this->prefix}nonconformities 
            SET status = 'car_issued', car_form_id = ?, updated_at = NOW()
            WHERE id = ?
        ")->execute([$carId, $ncId]);

        // تولید اقدامات پیش‌فرض بر اساس شدت
        $this->generateDefaultActions($carId, $nc['severity'], $userId);

        return [
            'success' => true,
            'car_id' => $carId,
            'car_number' => $carNumber,
            'message' => 'CAR با موفقیت ایجاد شد.'
        ];
    }

    /**
     * پیشنهاد اقدام اصلاحی بر اساس شدت
     */
    private function suggestCorrectiveAction($severity, $title)
    {
        $suggestions = [
            'minor' => "بررسی ریشه‌ای مشکل '{$title}' و انجام اقدامات اصلاحی مناسب. آموزش پرسنل مرتبط و به‌روزرسانی مستندات در صورت نیاز.",
            'major' => "انجام تحلیل ریشه‌ای کامل (5Whys یا Fishbone) برای مشکل '{$title}'. تشکیل تیم اصلاح، تعیین مسئول و زمان‌بندی دقیق. بازنگری فرآیندهای مرتبط و به‌روزرسانی رویه‌ها.",
            'critical' => "اقدام فوری برای کنترل مشکل '{$title}'. تشکیل تیم ویژه با حضور مدیریت ارشد. تحلیل ریشه‌ای جامع، بازطراحی فرآیند، آموزش گسترده و پایش مستمر اثربخشی اقدامات."
        ];
        return $suggestions[$severity] ?? "بررسی و رفع مشکل '{$title}' با رویکرد سیستماتیک.";
    }

    /**
     * تولید اقدامات پیش‌فرض
     */
    private function generateDefaultActions($carId, $severity, $userId)
    {
        $actions = [
            ['تحلیل ریشه‌ای مشکل', 'انجام تحلیل ریشه‌ای با استفاده از تکنیک 5Whys یا نمودار استخوان ماهی', 'corrective', 'high', 7],
            ['تدوین برنامه اقدام', 'تهیه برنامه اقدام اصلاحی با تعیین مسئول، منابع و زمان‌بندی', 'corrective', 'high', 5],
            ['اجرای اقدامات', 'پیاده‌سازی اقدامات اصلاحی تعریف شده', 'corrective', 'medium', 14],
            ['پایش اثربخشی', 'ارزیابی اثربخشی اقدامات انجام شده پس از ۳۰ روز', 'verification', 'medium', 30]
        ];

        if ($severity === 'critical') {
            array_unshift($actions, ['اقدام فوری موقت', 'انجام اقدامات موقت برای کنترل فوری مشکل', 'immediate', 'critical', 1]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}car_actions 
            (car_form_id, action_number, action_title, action_description, action_type, 
             priority, responsible_person_id, due_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), 'pending', NOW())
        ");

        foreach ($actions as $i => $action) {
            $stmt->execute([
                $carId,
                $i + 1,
                $action[0],
                $action[1],
                $action[2],
                $action[3],
                $userId,
                $action[4]
            ]);
        }
    }
}