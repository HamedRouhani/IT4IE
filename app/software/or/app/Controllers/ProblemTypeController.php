<?php
/**
 * OR Analyzer - کنترلر انواع مسائل
 * مسیر: app/software/or/app/Controllers/ProblemTypeController.php
 */

namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\ProblemType;
use App\Software\Or\Models\Method;

class ProblemTypeController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new ProblemType();
    }

    /**
     * لیست انواع مسائل (صفحه اصلی کارت‌ها)
     */
    public function index()
    {
        $this->view('problem_type/index', [
            'pageTitle'    => 'انواع مسئله',
            'currentPage'  => 'problem_type',
            'problemTypes' => $this->model->getAll(),
        ]);
    }

    /**
     * متد عمومی هدایت به ماژول مربوطه
     * ✅ نکته کلیدی: به جای action=create، به صفحه اصلی ماژول مقصد هدایت می‌شود
     * تا از خطای "متد یافت نشد" در کنترلرهای جدید جلوگیری شود.
     */
    public function create()
    {
        $this->requireAuth();
        $type = $_GET['type'] ?? $_POST['type'] ?? '';
        
        $controllerMap = [
            'LP'          => 'simplex',
            'TRANS'       => 'transport',
            'ASSIGN'      => 'assignment',
            'TRANSSHIP'   => 'transship',
            'SHORTEST'    => 'shortest',
            'QUEUEING'    => 'queueing',
            'MONTE_CARLO' => 'monte_carlo',
            'MARKOV'      => 'markov',
            'GAME_THEORY' => 'game_theory',
            'DUAL'        => 'dual',
            'ILP'         => 'ilp',
        ];
        
        if (empty($type) || !isset($controllerMap[$type])) {
            $this->flashError('نوع مسئله مشخص نشده یا نامعتبر است.');
            $this->redirect('controller=problem_type');
            return;
        }
        
        $targetController = $controllerMap[$type];
        
        // هدایت ایمن به صفحه اصلی ماژول مقصد (بدون نیاز به متد create در آن ماژول)
        $this->redirect("controller={$targetController}");
    }

    /**
     * نمایش جزئیات یک نوع مسئله + روش‌های مرتبط
     */
    public function show($id)
    {
        $pt = $this->model->find((int)$id);
        if (!$pt) {
            $this->flashError('نوع مسئله یافت نشد.');
            $this->redirect('controller=problem_type');
        }

        $methods = (new Method())->getByProblemType((int)$id);

        $this->view('problem_type/show', [
            'pageTitle'    => $pt['name_fa'],
            'currentPage'  => 'problem_type',
            'problemType'  => $pt,
            'methods'      => $methods,
        ]);
    }

    /**
     * خروجی JSON برای فیلتر داینامیک (AJAX)
     */
    public function ajax()
    {
        $this->json([
            'success'      => true,
            'problemTypes' => $this->model->getAll(),
        ]);
    }
}