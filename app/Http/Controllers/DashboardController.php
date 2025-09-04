<?php



namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        
        $data = $this->dashboardService->getStats();

        return response()->json([
            'status' => 200,
            'data'   => $data,
        ], 200);
    }
}
