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
            'total_presensi_today' => Presensi::where('tanggal', date('Y-m-d'))->where('status', 'Hadir')->count(),
            'total_alfa_today' => Presensi::where('tanggal', date('Y-m-d'))->where('status', 'Alfa')->count(),
        ];

        return view('superadmin.dashboard', compact('stats'));
    }
}
