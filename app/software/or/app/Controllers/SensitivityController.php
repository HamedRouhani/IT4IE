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

    public function analyze($id)
    {
        $this->requireAuth();
        $p = $this->model->find((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id'])) {
            $this->json(['success' => false, 'error' => 'دسترسی مجاز نیست.'], 403);
        }

        if ($p['status'] !== 'solved') {
            $this->json(['success' => false, 'error' => 'پروژه باید ابتدا حل شود.'], 400);
        }

        $result = json_decode($p['solution_data'], true);
        $metadata = json_decode($p['metadata'], true);

        if (!$result || !$metadata) {
            $this->json(['success' => false, 'error' => 'داده‌های حل مدل یافت نشد.'], 404);
        }

        $sensitivity = SensitivityAnalyzer::analyze($result, $metadata['c'], $metadata['b'], $metadata['types'], $p['objective']);
        $this->json(['success' => true, 'sensitivity' => $sensitivity]);
    }
}