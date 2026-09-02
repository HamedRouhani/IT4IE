<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Helpers\SensitivityAnalyzer;

class SensitivityController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->requireAuth();
        $projects = $this->model->getSolvedProjects($this->currentUserId);
        
        $this->view('sensitivity/index', [
            'pageTitle'   => 'تحلیل حساسیت',
            'currentPage' => 'sensitivity',
            'projects'    => $projects,
        ]);
    }

    public function report($id) 
    {
        $this->requireAuth();
        $id = (int)$id;
        
        // ۱. دریافت اطلاعات پروژه با جزئیات
        $project = $this->model->getWithDetails($id);

        if (!$project || $project['user_id'] != $this->currentUserId) {
            $this->flashError('پروژه مورد نظر یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=sensitivity');
            return;
        }

        $problemType = $project['problem_type_code'] ?? 'UNKNOWN';
        
        // ۲. دریافت داده‌های مدل و جواب بهینه
        $modelData = json_decode($project['model_data'] ?? '{}', true);
        $solution  = json_decode($project['solution_data'] ?? '{}', true);

        // ۳. بررسی حل شدن مسئله
        if (empty($solution) || $project['status'] !== 'solved') {
            $this->flashError('برای این پروژه هنوز جواب بهینه‌ای ثبت نشده است. لطفاً ابتدا مسئله را حل کنید.');
            $this->redirect('controller=sensitivity');
            return;
        }

        // ۴. فراخوانی Helper برای تولید خودکار تحلیل حساسیت
        $analysis = SensitivityAnalyzer::analyze($problemType, $solution, $modelData);

        // ۵. رندر کردن ویو با نام متغیرهای دقیقاً مطابق انتظار فایل view
        $this->view('sensitivity/report', [
            'pageTitle'   => 'گزارش تحلیل حساسیت: ' . $project['name'],
            'currentPage' => 'sensitivity',
            'project'     => $project,
            'problemType' => $problemType,  // مورد انتظار ویو
            'analysis'    => $analysis,     // مورد انتظار ویو (باید status => success داشته باشد)
            'modelData'   => $modelData,    // مورد انتظار partialها (مثل _lp_analysis.php)
            'solution'    => $solution,     // مورد انتظار partialها
        ]);
    }
}