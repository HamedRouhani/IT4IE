<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Method;
use App\Software\Or\Models\ProblemType;

class MethodController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Method();
    }

    public function index()
    {
        $ptId = (int)($_GET['problem_type_id'] ?? 0);
        $cat  = $_GET['category'] ?? null;

        $this->view('method/index', [
            'pageTitle'    => 'روش‌های حل',
            'currentPage'  => 'method',
            'methods'      => $this->model->getWithProblemType($ptId > 0 ? $ptId : null, $cat),
            'problemTypes' => (new ProblemType())->getAll(),
            'filterType'   => $ptId,
            'filterCat'    => $cat,
        ]);
    }

    public function show($id)
    {
        $m = $this->model->getWithDetails((int)$id);
        if (!$m) { $this->flashError('روش یافت نشد.'); $this->redirect('controller=method'); }

        $this->view('method/view', [
            'pageTitle'   => $m['name_fa'],
            'currentPage' => 'method',
            'method'      => $m,
        ]);
    }

    public function ajax()
    {
        $ptId = (int)($_GET['problem_type_id'] ?? 0);
        if ($ptId <= 0)
            $this->json(['success'=>false,'error'=>'نوع مسئله مشخص نشده است.']);
        $this->json(['success'=>true, 'methods'=>$this->model->getByProblemType($ptId)]);
    }
}