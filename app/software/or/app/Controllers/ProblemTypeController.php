<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\Method;

class ProblemTypeController extends Controller
{
    public function index()
    {
        // اختیاری: اگر می‌خواهید این صفحه فقط برای کاربران لاگین‌کرده باشد، خط زیر را فعال کنید
        // $this->requireAuth();

        $ptModel = new ProblemType();
        $methodModel = new Method();

        $problemTypes = $ptModel->getAll();
        
        // دریافت روش‌های حل مرتبط با هر نوع مسئله برای نمایش در کارت‌ها
        foreach ($problemTypes as &$pt) {
            $pt['methods'] = $methodModel->getByProblemType($pt['id']);
        }

        $this->view('problem_type/index', [
            'pageTitle'    => 'انواع مسئله (Problem Types)',
            'currentPage'  => 'problem_type',
            'problemTypes' => $problemTypes
        ]);
    }
}