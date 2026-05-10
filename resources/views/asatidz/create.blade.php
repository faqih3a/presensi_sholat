@extends('layouts.app')

@section('title', 'Tambah Asatidz')

@section('content')
<div class="row">
    <div class="col-12 mb-4">
        <a href="{{ route('asatidz.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
        </a>
        <h4 class="fw-800 text-dark mb-1">Tambah Asatidz Baru</h4>
        <p class="text-muted small">Buat akun untuk pengajar baru di sistem.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <form action="{{ route('asatidz.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">NAMA LENGKAP</label>
                    <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Subagja">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">ALAMAT EMAIL</label>
                    <input type="email" name="email" class="form-control rounded-3 @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="email@example.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">NOMOR WHATSAPP</label>
                    <input type="text" name="wa_number" class="form-control rounded-3 @error('wa_number') is-invalid @enderror" value="{{ old('wa_number') }}" placeholder="Contoh: 628123456789">
                    <small class="text-muted mt-1 d-block">Gunakan format internasional tanpa tanda + (Contoh: 628123456789)</small>
                    @error('wa_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">PASSWORD</label>
                        <input type="password" name="password" class="form-control rounded-3 @error('password') is-invalid @enderror" required placeholder="Minimal 5 karakter">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">KONFIRMASI PASSWORD</label>
                        <input type="password" name="password_confirmation" class="form-control rounded-3" required placeholder="Ulangi password">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 w-100 fw-bold">
                        BUAT AKUN ASATIDZ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
