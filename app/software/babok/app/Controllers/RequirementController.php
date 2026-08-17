<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Services\RequirementService;

/**
 * کنترلر استخراج و تحلیل یکپارچه نیازمندی
 * وابسته به: RequirementService (مرحله ۸)
 * 
 * قابلیت‌ها:
 * - استخراج نیازمندی از متن فارسی
 * - تحلیل و پیشنهاد تکنیک‌های BABOK
 * - شناسایی حوزه کاری (مالی، تولید، خدمات و...)
 * - پشتیبانی از ورودی صوتی (در فرانت‌اند)
 */
class RequirementController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->service = new RequirementService();
    }

    /**
     * صفحه اصلی استخراج و تحلیل نیازمندی
     * مسیر: /software/babok/?route=requirement
     */
    public function index()
    {
        $this->view('requirement/index', [
            'title' => 'استخراج و تحلیل نیازمندی - BABOK Analyzer',
            'activePage' => 'requirement'
        ]);
    }

    /**
     * صفحه نتایج استخراج و تحلیل نیازمندی
     * مسیر: /software/babok/?route=requirement_result
     */
    public function result()
    {
        $this->view('requirement/result', [
            'title' => 'نتایج تحلیل نیازمندی - BABOK Analyzer',
            'activePage' => 'requirement'
        ]);
    }

    /**
     * پردازش یکپارچه: استخراج + تحلیل
     * مسیر: /software/babok/?route=requirement_analyze (POST)
     * 
     * ورودی: JSON با فیلد text
     * خروجی: JSON شامل نیازمندی‌ها، تکنیک‌ها، حوزه کاری و پیشنهاد
     */
    public function analyze()
    {
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $text = trim($input['text'] ?? '');

            if (empty($text)) {
                throw new \Exception('لطفاً متن نیازمندی را وارد کنید.');
            }

            if (mb_strlen($text) < 10) {
                throw new \Exception('متن باید حداقل ۱۰ کاراکتر باشد.');
            }

            // محدودیت طول متن برای جلوگیری از حملات
            if (mb_strlen($text) > 10000) {
                throw new \Exception('متن نباید بیشتر از ۱۰۰۰۰ کاراکتر باشد.');
            }

            $result = $this->service->process($text);

            // ثبت لاگ فعالیت
            $this->logActivity('analyze_requirement', 'requirement', null, null, [
                'text_length' => mb_strlen($text),
                'requirements_found' => $result['stats']['total'] ?? 0,
                'techniques_found' => $result['stats']['techniques'] ?? 0,
                'domain' => $result['domain'] ?? 'عمومی'
            ]);

            echo json_encode([
                'success' => true,
                'data' => $result
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * اندپوینت AJAX برای اعتبارسنجی آنی نیازمندی
     */
    public function validateAjax()
    {
        // فقط درخواست‌های POST را بپذیر
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            return;
        }

        // دریافت و پاکسازی ورودی‌ها
        $text = trim($_POST['text'] ?? '');
        $methodology = $_POST['methodology'] ?? 'hybrid';

        if (empty($text)) {
            echo json_encode(['score' => 0, 'issues' => [], 'suggestions' => []]);
            return;
        }

        // فراخوانی سرویس
        $service = new RequirementService();
        $result = $service->validateRequirementQuality($text, $methodology);

        // بازگرداندن پاسخ JSON
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * اعتبارسنجی آنی کیفیت نیازمندی (AJAX Endpoint)
     */
    public function validateQualityAjax()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'Method Not Allowed'], 405);
        }

        $text = trim($_POST['text'] ?? '');
        $methodology = $_POST['methodology'] ?? 'hybrid';

        if (empty($text)) {
            return $this->json([
                'score' => 0,
                'grade' => 'خالی',
                'issues' => [],
                'suggestions' => [],
                'is_valid' => false
            ]);
        }

        // فراخوانی سرویسی که قبلاً ساختیم
        $service = new \App\Software\Babok\Services\RequirementService();
        $result = $service->validateRequirementQuality($text, $methodology);

        return $this->json($result);
    }
}