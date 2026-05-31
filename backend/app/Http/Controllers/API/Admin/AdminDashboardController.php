<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function stats(): JsonResponse
    {
        return response()->json($this->dashboardService->getStats());
    }
}
