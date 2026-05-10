<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AsatidzController extends Controller
{
    public function index()
    {
        $asatidz = User::where('role', 'asatidz')->latest()->get();
        return view('asatidz.index', compact('asatidz'));
    }

    public function create()
    {
        return view('asatidz.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'wa_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:5|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'wa_number' => $request->wa_number,
            'password' => Hash::make($request->password),
            'role' => 'asatidz',
        ]);

        return redirect()->route('asatidz.index')->with('success', 'Akun Asatidz berhasil dibuat.');
    }

    public function edit(User $asatidz)
    {
        if ($asatidz->role !== 'asatidz') {
            return redirect()->route('asatidz.index')->with('error', 'User bukan merupakan asatidz.');
        }
        return view('asatidz.edit', compact('asatidz'));
    }

    public function update(Request $request, User $asatidz)
    {
        if ($asatidz->role !== 'asatidz') {
            return redirect()->route('asatidz.index')->with('error', 'User bukan merupakan asatidz.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $asatidz->id,
            'wa_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:5|confirmed',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'wa_number' => $request->wa_number,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $asatidz->update($data);

        return redirect()->route('asatidz.index')->with('success', 'Data Asatidz berhasil diperbarui.');
    }

    public function destroy(User $asatidz)
    {
        if ($asatidz->role !== 'asatidz') {
            return redirect()->route('asatidz.index')->with('error', 'User bukan merupakan asatidz.');
        }

        $asatidz->delete();

        return redirect()->route('asatidz.index')->with('success', 'Akun Asatidz berhasil dihapus.');
    }
}
