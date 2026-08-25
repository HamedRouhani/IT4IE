<?php
namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Industry;

class IndustryController extends Controller
{
    public function index()
    {
        $industryModel = new Industry();
        $industries = $industryModel->getAll();
        
        $this->view('industry/index', [
            'pageTitle' => 'صنایع',
            'currentPage' => 'industry',
            'industries' => $industries,
        ]);
    }
    
    public function show($id)
    {
        $industryModel = new Industry();
        $industry = $industryModel->getWithDetails((int)$id);
        
        if (!$industry) {
            $this->flashError('صنعت یافت نشد.');
            $this->redirect('controller=industry');
        }
        
        $this->view('industry/view', [
            'pageTitle' => $industry['name_fa'] ?? $industry['name_en'] ?? 'صنعت',
            'currentPage' => 'industry',
            'industry' => $industry,
            'recommendedMethods' => $industryModel->getRecommendedMethods((int)$id),
        ]);
    }
}