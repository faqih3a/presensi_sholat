<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'wa_number' => ['nullable', 'string', 'max:20'],
            'kelas' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'wa_number' => $request->wa_number,
        ];

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            
            if ($user->role === 'santri') {
                $file->move(public_path('storage/santri_fotos'), $filename);
                if ($user->santri) {
                    $user->santri->update(['foto_referensi' => $filename]);
                }
            } else {
                $file->move(public_path('storage/avatars'), $filename);
                $userData['avatar'] = $filename;
            }
        }

        $user->update($userData);

        // If santri, update their name and class in santris table as well
        if ($user->role === 'santri' && $user->santri) {
            $user->santri->update([
                'nama' => $request->name,
                'kelas' => $request->kelas ?? $user->santri->kelas,
            ]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:5'],
        ], [
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal harus 5 karakter.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
