@extends('layouts.site')

@section('title', 'Đặt lại mật khẩu - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-4"><i class="bi bi-shield-lock"></i> Đặt lại mật khẩu</h4>

                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới</label>
                        <input type="password" name="password" required autocomplete="new-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nhập lại mật khẩu mới</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Đặt lại mật khẩu</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
