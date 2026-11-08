<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    @stack('styles')
</head>

<body class="bg-body-tertiary">

    {{-- Thanh điều hướng ngang --}}
    <nav class="navbar navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-2">
                {{-- Nút đóng/mở menu bên trái --}}
                <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#adminMenu" aria-controls="adminMenu" title="Mở menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <a class="navbar-brand mb-0 ms-1" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2 text-warning"></i> TRANG QUẢN TRỊ
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a class="text-white-50 text-decoration-none small d-none d-sm-inline" href="{{ route('home') }}"
                    target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> Xem website
                </a>
                <span class="text-white small d-none d-md-inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm">Đăng xuất</button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Menu trượt từ bên trái --}}
    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="adminMenu" aria-labelledby="adminMenuLabel">
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title" id="adminMenuLabel">
                <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body">
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house me-2"></i> Tổng quan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.comics.*', 'admin.chapters.*') ? 'active' : 'text-white' }}"
                        href="{{ route('admin.comics.index') }}">
                        <i class="bi bi-journal-richtext me-2"></i> Truyện &amp; chương
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : 'text-white' }}"
                        href="{{ route('admin.categories.index') }}">
                        <i class="bi bi-tags me-2"></i> Thể loại
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : 'text-white' }}"
                        href="{{ route('admin.authors.index') }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Tác giả
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : 'text-white' }}"
                        href="{{ route('admin.comments.index') }}">
                        <i class="bi bi-chat-dots me-2"></i> Bình luận
                    </a>
                </li>
            </ul>

            <hr class="border-secondary my-3">

            <a class="nav-link text-white-50" href="{{ route('home') }}" target="_blank">
                <i class="bi bi-box-arrow-up-right me-2"></i> Xem website
            </a>
        </div>
    </div>

    {{-- Nội dung chiếm toàn bộ chiều rộng --}}
    <main class="container-fluid px-3 px-md-4 py-4">
        @include('partials.flash')
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>