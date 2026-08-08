<?php

namespace App\Controllers;

use App\Services\RequirementService;

class RequirementController
{
    private RequirementService $service;

    public function __construct()
    {
        $this->service = new RequirementService();
    }

    /**
     * صفحه اصلی استخراج و تحلیل نیازمندی
     * مسیر: GET /?route=requirement
     */
    public function index()
    {
        require_once __DIR__ . '/../../views/requirement/index.php';
    }

    /**
     * صفحه نتایج استخراج و تحلیل نیازمندی
     * مسیر: GET /?route=requirement_result
     */
    public function result()
    {
        require_once __DIR__ . '/../../views/requirement/result.php';
    }

    /**
     * پردازش یکپارچه: استخراج + تحلیل
     * مسیر: POST /?route=requirement_analyze
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
            
            if (strlen($text) < 10) {
                throw new \Exception('متن باید حداقل ۱۰ کاراکتر باشد.');
            }

            $result = $this->service->process($text);

            echo json_encode([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}