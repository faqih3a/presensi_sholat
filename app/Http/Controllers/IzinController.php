<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Izin;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index()
    {
        $izins = Izin::where('user_id', auth()->id())->latest()->get();
        return view('izin.index', compact('izins'));
    }

    public function create()
    {
        return view('izin.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_izin' => 'required|in:Sakit,Izin,Kegiatan Luar',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('lampiran')) {
            $path = $request->file('lampiran')->store('lampiran_izin', 'public');
            $data['lampiran'] = $path;
        }

        $izin = Izin::create($data);

        // Send WhatsApp Notification to Asatidz
        try {
            $asatidz = \App\Models\User::where('role', 'asatidz')
                                      ->whereNotNull('wa_number')
                                      ->get();
            
            if ($asatidz->count() > 0) {
                $message = \App\Services\WhatsAppService::formatIzinNotification($izin);
                foreach ($asatidz as $ustadz) {
                    \App\Services\WhatsAppService::sendMessage($ustadz->wa_number, $message);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send WA notification: ' . $e->getMessage());
        }

        return redirect()->route('izin.index')->with('success', 'Permohonan izin berhasil diajukan.');
    }

    public function manage()
    {
        // Only for Asatidz
        if (auth()->user()->role !== 'asatidz') {
            abort(403);
        }

        $izins = Izin::with('user.santri')->latest()->get();
        return view('izin.manage', compact('izins'));
    }

    public function updateStatus(Request $request, Izin $izin)
    {
        if (auth()->user()->role !== 'asatidz') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:Disetujui,Ditolak',
            'keterangan_admin' => 'nullable|string',
        ]);

        $izin->update([
            'status' => $request->status,
            'keterangan_admin' => $request->keterangan_admin,
        ]);

        return redirect()->back()->with('success', 'Status permohonan izin berhasil diperbarui.');
    }
}
