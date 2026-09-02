<?php
/**
 * OR Analyzer - کنترلر انواع مسائل
 * مسیر: app/software/or/app/Controllers/ProblemTypeController.php
 * URL: /software/or-analyzer/?controller=problem_type
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
     * لیست انواع مسائل
     * URL: ?controller=problem_type
     */
    public function index()
    {
        $this->view('problem_type/index', [
            'pageTitle'    => 'انواع مسائل',
            'currentPage'  => 'problem_type',
            'problemTypes' => $this->model->getAll(),
        ]);
    }

    /**
     * نمایش جزئیات یک نوع مسئله + روش‌های مرتبط
     * URL: ?controller=problem_type&action=show&id=1
     */
    public function show($id)
    {
        $pt = $this->model->find((int)$id);
        if (!$pt) {
            $this->flashError('نوع مسئله یافت نشد.');
            $this->redirect('controller=problem_type');
        }

        // دریافت روش‌های حل مرتبط با این نوع مسئله
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
     * URL: ?controller=problem_type&action=ajax
     */
    public function ajax()
    {
        $this->json([
            'success'      => true,
            'problemTypes' => $this->model->getAll(),
        ]);
    }
}