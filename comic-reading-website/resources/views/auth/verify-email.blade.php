@extends('layouts.site')

@section('title', 'Xác minh email - ' . config('app.name'))

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="fw-bold text-center mb-3"><i class="bi bi-envelope-check"></i> Xác minh email</h4>
                <p class="text-muted">
                    Cảm ơn bạn đã đăng ký! Vui lòng bấm vào liên kết chúng tôi vừa gửi đến email của bạn để xác minh.
                    Nếu chưa nhận được, bạn có thể yêu cầu gửi lại.
                </p>

                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success">Đã gửi liên kết xác minh mới đến email của bạn.</div>
                @endif

                <div class="d-flex justify-content-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="btn btn-primary">Gửi lại email xác minh</button>
                    </form>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
