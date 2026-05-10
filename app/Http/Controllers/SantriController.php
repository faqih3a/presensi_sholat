<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\Santri;
use App\Models\User;

class SantriController extends Controller
{
    public function create()
    {
        return view('santri.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'foto_referensi' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'face_descriptor' => 'required|string',
        ]);

        $imagePath = $request->file('foto_referensi')->store('santri_fotos', 'public');
        $fileName = basename($imagePath);

        // Buat akun User untuk santri
        $user = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'santri',
        ]);

        $santri = Santri::create([
            'user_id' => $user->id,
            'nama' => $request->nama,
            'kelas' => $request->kelas,
            'foto_referensi' => $fileName,
            'face_descriptor' => $request->face_descriptor,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data santri dan akun berhasil didaftarkan.',
            'data' => $santri
        ]);
    }

    public function index()
    {
        $santris = Santri::all();
        return response()->json($santris);
    }

    public function adminList()
    {
        $santris = Santri::latest()->get();
        return view('santri.index', compact('santris'));
    }

    public function edit(Santri $santri)
    {
        return view('santri.edit', compact('santri'));
    }

    public function update(Request $request, Santri $santri)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kelas' => 'required|string|max:50',
            'foto_referensi' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = [
            'nama' => $request->nama,
            'kelas' => $request->kelas,
        ];

        if ($request->hasFile('foto_referensi') && $request->filled('face_descriptor')) {
            if ($santri->foto_referensi && Storage::disk('public')->exists('santri_fotos/' . $santri->foto_referensi)) {
                Storage::disk('public')->delete('santri_fotos/' . $santri->foto_referensi);
            }
            $imagePath = $request->file('foto_referensi')->store('santri_fotos', 'public');
            $data['foto_referensi'] = basename($imagePath);
            $data['face_descriptor'] = $request->face_descriptor;
        }

        $santri->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data santri berhasil diperbarui.',
                'data' => $santri
            ]);
        }

        return redirect()->route('santri.index')->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Santri $santri)
    {
        if ($santri->foto_referensi && Storage::disk('public')->exists('santri_fotos/' . $santri->foto_referensi)) {
            Storage::disk('public')->delete('santri_fotos/' . $santri->foto_referensi);
        }

        $user = $santri->user;
        $santri->delete();
        
        if ($user) {
            $user->delete();
        }

        return redirect()->route('santri.index')->with('success', 'Data santri berhasil dihapus.');
    }
}
