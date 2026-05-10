<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Santri;
use App\Models\Presensi;
use Illuminate\Http\Request;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_asatidz' => User::where('role', 'asatidz')->count(),
            'total_santri' => Santri::count(),
        ];

        return view('superadmin.dashboard', compact('stats'));
    }
}
