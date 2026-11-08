@extends('layouts.site')

@section('title', 'Quên mật khẩu - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-3"><i class="bi bi-key"></i> Quên mật khẩu</h4>
                <p class="text-muted small">Nhập email của bạn, chúng tôi sẽ gửi liên kết để đặt lại mật khẩu.</p>

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-primary w-100">Gửi liên kết đặt lại mật khẩu</button>
                </form>

                <p class="text-center small mt-3 mb-0"><a href="{{ route('login') }}">Quay lại đăng nhập</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
