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

    public function report($id) {
        // 1. اعتبارسنجی ورودی و دریافت اطلاعات پروژه
        $id = (int)$id;
        if ($id <= 0) {
            $this->error('شناسه پروژه نامعتبر است.');
            return;
        }

        // استفاده از Model های پروژه
        $projectModel = new Project();
        $project = $projectModel->find($id);

        if (!$project) {
            $this->error('پروژه مورد نظر یافت نشد.');
            return;
        }

        // 🔒 بررسی دسترسی کاربر به پروژه
        if (isset($_SESSION['user_id']) && $project['user_id'] != $_SESSION['user_id']) {
            $this->error('شما دسترسی لازم برای مشاهده این گزارش را ندارید.');
            return;
        }

        // 2. دریافت اطلاعات کامل مسئله (نوع مسئله، متد حل، جواب بهینه و...)
        $problemTypeModel = new ProblemType();
        $problemType = $problemTypeModel->find($project['problem_type_id']);

        $methodModel = new Method();
        $method = null;
        if ($project['method_id']) {
            $method = $methodModel->find($project['method_id']);
        }

        $resultModel = new Result();
        $optimalResult = $resultModel->getProjectResult($id);

        if (!$optimalResult) {
            $this->error('برای این پروژه هنوز جواب بهینه‌ای ثبت نشده است. لطفاً ابتدا مسئله را حل کنید.');
            return;
        }

        // 3. آماده‌سازی داده‌های پایه برای تحلیل حساسیت
        $nodesModel = new Node();
        $nodes = $nodesModel->getProjectNodes($id);

        $edgesModel = new Edge();
        $edges = $edgesModel->getProjectEdges($id);

        $allocationsModel = new Allocation();
        $allocations = $allocationsModel->getProjectAllocations($id);

        // 4. تولید گزارش بر اساس نوع مسئله
        $reportData = [];
        $reportType = $problemType['code'] ?? 'UNKNOWN';

        switch ($reportType) {
            case 'TRANS': // مسئله حمل و نقل
            case 'TRANSSHIP': // مسئله ترانشیپمنت
                $reportData = $this->generateTransportationSensitivityReport($project, $nodes, $edges, $allocations, $optimalResult);
                break;
            
            case 'ASSIGN': // مسئله تخصیص
                $reportData = $this->generateAssignmentSensitivityReport($project, $nodes, $edges, $allocations, $optimalResult);
                break;

            case 'SHORTEST': // مسئله کوتاه‌ترین مسیر
                $reportData = $this->generateShortestPathSensitivityReport($project, $nodes, $edges, $optimalResult);
                break;

            case 'LP': // برنامه‌ریزی خطی (Simplex)
                $reportData = $this->generateLPSensitivityReport($project, $optimalResult);
                break;

            default:
                $this->error('نوع مسئله برای تحلیل حساسیت پشتیبانی نمی‌شود.');
                return;
        }

        // 5. رندر کردن ویو
        $this->render('sensitivity/report', [
            'project' => $project,
            'problemType' => $problemType,
            'method' => $method,
            'optimalResult' => $optimalResult,
            'reportData' => $reportData,
            'reportType' => $reportType
        ]);
    }
}