@extends('layouts.site')

@section('title', 'Xác nhận mật khẩu - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-3"><i class="bi bi-lock"></i> Xác nhận mật khẩu</h4>
                <p class="text-muted small">Đây là khu vực bảo mật. Vui lòng nhập lại mật khẩu để tiếp tục.</p>

                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu</label>
                        <input type="password" name="password" required autofocus autocomplete="current-password"
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary w-100">Xác nhận</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
