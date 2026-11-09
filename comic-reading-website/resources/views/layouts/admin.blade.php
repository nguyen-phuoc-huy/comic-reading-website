<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        /* Nút mũi tên mở/đóng menu (chỉ hiện trên điện thoại), xoay lên khi menu đang mở */
        .menu-toggle i {
            display: inline-block;
            transition: transform .25s;
        }

        .menu-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        /* ===== Thanh menu nằm dưới thanh đen ===== */
        .admin-menu .nav-link {
            color: var(--bs-body-color);
            font-weight: 500;
            padding: .65rem .9rem;
            border-bottom: 3px solid transparent;
        }

        .admin-menu .nav-link:hover {
            color: var(--bs-primary);
        }

        .admin-menu .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
        }

        /* Điện thoại: menu là danh sách dọc trượt xuống */
        @media (max-width: 991.98px) {
            #adminMenu {
                max-height: calc(100vh - 4rem);
                overflow-y: auto;
            }

            .admin-menu .nav-link {
                padding: .6rem .5rem;
                border-bottom: 0;
                border-left: 3px solid transparent;
            }

            .admin-menu .nav-link.active {
                border-left-color: var(--bs-primary);
                background: var(--bs-primary-bg-subtle);
            }
        }
    </style>
    @stack('styles')
</head>

<body class="bg-body-tertiary">

    <header class="sticky-top shadow-sm">

        {{-- ===== Thanh đen trên cùng ===== --}}
        <nav class="navbar navbar-dark bg-dark py-2">
            <div class="container-fluid px-3 px-md-4">
                <a class="navbar-brand mb-0" href="{{ route('admin.dashboard') }}">
                    <i class="bi bi-speedometer2 text-warning"></i> Quản trị
                </a>

                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <a class="text-white-50 text-decoration-none small d-none d-md-inline" href="{{ route('home') }}"
                        target="_blank">
                        <i class="bi bi-box-arrow-up-right"></i> Xem website
                    </a>
                    <span class="text-white small d-none d-md-inline">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-light btn-sm" title="Đăng xuất">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline">Đăng xuất</span>
                        </button>
                    </form>

                    {{-- Nút mũi tên: mở/đóng menu (điện thoại) --}}
                    <button class="menu-toggle btn btn-outline-light btn-sm d-lg-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#adminMenu" aria-controls="adminMenu"
                        aria-expanded="false" aria-label="Mở menu">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
        </nav>

        {{-- ===== Menu: máy tính hiện sẵn dưới thanh đen, điện thoại bấm mũi tên để trượt xuống ===== --}}
        <div class="collapse d-lg-block" id="adminMenu">
            <nav class="admin-menu bg-body border-bottom" aria-label="Menu quản trị">
                <div class="container-fluid px-3 px-md-4">
                    <ul class="nav flex-column flex-lg-row flex-lg-wrap">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-house"></i> Tổng quan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.comics.*', 'admin.chapters.*') ? 'active' : '' }}"
                                href="{{ route('admin.comics.index') }}">
                                <i class="bi bi-journal-richtext"></i> Truyện &amp; chương
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                                href="{{ route('admin.categories.index') }}">
                                <i class="bi bi-tags"></i> Thể loại
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.authors.*') ? 'active' : '' }}"
                                href="{{ route('admin.authors.index') }}">
                                <i class="bi bi-person-lines-fill"></i> Tác giả
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}"
                                href="{{ route('admin.comments.index') }}">
                                <i class="bi bi-chat-dots"></i> Bình luận
                            </a>
                        </li>
                        @if (Route::has('admin.imports.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.imports.*') ? 'active' : '' }}"
                                href="{{ route('admin.imports.index') }}">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Nhập dữ liệu Excel
                            </a>
                        </li>
                        @endif

                        {{-- Điện thoại: liên kết "Xem website" ở thanh đen bị ẩn nên đưa vào menu --}}
                        <li class="nav-item d-md-none">
                            <a class="nav-link" href="{{ route('home') }}" target="_blank">
                                <i class="bi bi-box-arrow-up-right"></i> Xem website
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    {{-- Nội dung chiếm toàn bộ chiều rộng (không còn thanh bên trái) --}}
    <main class="container-fluid px-3 px-md-4 py-4">
        @include('partials.flash')
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>