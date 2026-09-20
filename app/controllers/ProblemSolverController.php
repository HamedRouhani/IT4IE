<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\ProblemIntelligenceService;

class ProblemSolverController extends Controller
{
    public function analyze()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'success' => false,
                'message' => 'روش درخواست نامعتبر است.'
            ]);
        }

        if (!$this->verifyCsrf()) {
            $this->json([
                'success' => false,
                'message' => 'درخواست نامعتبر است. صفحه را مجدداً بارگذاری کنید.'
            ]);
        }

        $problem = trim((string)($_POST['problem'] ?? ''));

        if ($problem === '') {
            $this->json([
                'success' => false,
                'message' => 'لطفاً مسئله خود را وارد کنید.'
            ]);
        }

        /*
         * محدودیت اولیه برای جلوگیری از ورودی‌های بسیار بزرگ
         */
        if (mb_strlen($problem, 'UTF-8') > 5000) {
            $this->json([
                'success' => false,
                'message' => 'توضیح مسئله بیش از حد طولانی است.'
            ]);
        }

        $service = new ProblemIntelligenceService();

        $result = $service->analyze($problem);

        /*
         * ذخیره موقت در Session
         * فعلاً بدون Login و بدون Database
         */
        $_SESSION['problem_solver'] = [
            'problem' => $problem,
            'result' => $result,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->json($result);
    }
}