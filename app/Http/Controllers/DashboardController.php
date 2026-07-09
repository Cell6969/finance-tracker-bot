<?php

namespace App\Http\Controllers;

use App\Service\DashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(Request $request): View
    {
        $guest = $request->user(); // Assuming authentication is handled
        $data = $this->dashboardService->getDashboardData($guest);

        return view('dashboard', compact('data'));
    }
}
