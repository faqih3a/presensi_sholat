@extends('layouts.app')

@section('title', 'Kelola Asatidz')

@section('content')
<div class="row">
    <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-800 text-dark mb-1">Kelola Asatidz</h4>
            <p class="text-muted small">Daftar semua akun asatidz (pengajar) yang terdaftar di sistem.</p>
        </div>
        <a href="{{ route('asatidz.create') }}" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-2"></i>Tambah Asatidz
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 1rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3">NAMA</th>
                    <th class="py-3">EMAIL</th>
                    <th class="py-3">TANGGAL DAFTAR</th>
                    <th class="py-3 text-end pe-4">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asatidz as $u)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                {{ substr($u->name, 0, 1) }}
                            </div>
                            <span class="fw-bold">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                    <td class="text-end pe-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('asatidz.edit', $u->id) }}" class="btn btn-sm btn-light rounded-2 text-primary">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('asatidz.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus asatidz ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light rounded-2 text-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">Belum ada data asatidz.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
