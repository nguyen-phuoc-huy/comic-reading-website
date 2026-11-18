<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var pref = 'auto';
            try {
                var saved = localStorage.getItem('theme');
                if (saved === 'light' || saved === 'dark') pref = saved;
            } catch (e) {}
            var theme = pref === 'auto'
                ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : pref;
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .comic-card img {
            aspect-ratio: 3 / 4;
            object-fit: cover;
        }

        .comic-card {
            transition: transform .15s;
        }

        .comic-card:hover {
            transform: translateY(-3px);
        }

        .comic-card .card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.6em;
            font-size: .9rem;
        }

        /* ===== Thanh trên cùng: tên website + (tìm kiếm) + tài khoản ===== */
        /* Điện thoại: ô tìm kiếm tự xuống dòng, nằm ngay dưới hàng tên website */
        .top-bar .top-search {
            flex: 1 0 100%;
        }

        @media (min-width: 992px) {
            .top-bar .top-search {
                flex: 1 1 auto;
            }
        }

        /* Nút mũi tên mở/đóng menu (chỉ hiện trên điện thoại), xoay lên khi menu đang mở */
        .menu-toggle i {
            display: inline-block;
            transition: transform .25s;
        }

        .menu-toggle[aria-expanded="true"] i {
            transform: rotate(180deg);
        }

        /* ===== Thanh menu thứ hai ===== */
        .subnav .container {
            position: relative;
        }

        .subnav .nav-link {
            color: var(--bs-body-color);
            font-weight: 500;
            padding: .65rem .9rem;
            border-bottom: 3px solid transparent;
        }

        .subnav .nav-link:hover {
            color: var(--bs-primary);
        }

        .subnav .nav-link.active {
            color: var(--bs-primary);
            border-bottom-color: var(--bs-primary);
        }

        /* Menu thể loại: một cột dọc trong ô giới hạn kích thước, cuộn được, chia nhóm theo chữ cái */
        .subnav .dropdown {
            position: relative;
        }

        .subnav .dropdown-menu.nav-categories {
            left: 0;
            right: auto;
            top: 100%;
            margin-top: 0;
            width: 260px;
            min-width: 0;
            max-height: 60vh;
            overflow-y: auto;
            padding: .25rem .5rem .5rem;
            border-radius: 0 0 .5rem .5rem;
        }

        .subnav .dropdown-menu.nav-categories.show {
            display: block;
        }

        .nav-categories .cat-all {
            margin: .25rem 0 .25rem;
        }

        .nav-categories .cat-letter {
            position: sticky;
            top: 0;
            z-index: 1;
            background: var(--bs-body-bg);
            font-weight: 700;
            font-size: 1.05rem;
            color: var(--bs-primary);
            border-bottom: 2px solid var(--bs-primary);
            margin: .5rem .5rem .25rem;
            padding-bottom: .1rem;
        }

        @media (min-width: 992px) {

            /* Rê chuột là hiện (desktop) */
            .subnav .nav-item.dropdown:hover>.nav-categories {
                display: block;
            }
        }

        /* Điện thoại: menu là danh sách dọc trượt xuống, thể loại bung ngay dưới mục "Thể loại" */
        @media (max-width: 991.98px) {
            #subMenu {
                max-height: calc(100vh - 6.5rem);
                overflow-y: auto;
            }

            .subnav .nav-link {
                padding: .6rem .5rem;
                border-bottom: 0;
                border-left: 3px solid transparent;
            }

            .subnav .nav-link.active {
                border-left-color: var(--bs-primary);
                background: var(--bs-primary-bg-subtle);
            }

            .subnav .dropdown-menu.nav-categories {
                position: static;
                float: none;
                width: 100%;
                max-height: 45vh;
                border: 1px solid var(--bs-border-color);
                box-shadow: none !important;
                padding: .25rem .5rem .5rem;
                border-radius: .5rem;
                margin-bottom: .5rem;
            }
        }

        /* ===== Thanh đầu trang tự ẩn khi cuộn xuống, hiện lại khi cuộn lên ===== */
        .site-header {
            transition: transform .3s ease;
            will-change: transform;
        }

        .site-header.header-hidden {
            transform: translateY(-100%);
        }

        @media (prefers-reduced-motion: reduce) {
            .site-header {
                transition: none;
            }
        }

        /* ===== Chế độ sáng / tối ===== */
        /* Các lớp màu cố định trong view: cho đổi màu theo chế độ tối */
        [data-bs-theme="dark"] .text-dark {
            color: var(--bs-body-color) !important;
        }

        [data-bs-theme="dark"] .text-bg-light {
            background-color: var(--bs-tertiary-bg) !important;
            color: var(--bs-body-color) !important;
        }

        /* ===== Nút mũi tên lên đầu trang ===== */
        .back-to-top {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 1030;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity .25s, transform .25s, visibility .25s;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: none;
        }
    </style>
    @stack('styles')
</head>

<body class="bg-body-tertiary d-flex flex-column min-vh-100">

    @php
    $navCategories = \App\Models\Category::orderBy('name')->get(['id', 'name', 'slug']);

    // Xác định mục đang được chọn trên thanh menu dưới
    $onIndex = request()->routeIs('comics.index');
    $qStatus = request('status');
    $qSort = request('sort');
    @endphp

    <header id="siteHeader" class="site-header sticky-top shadow-sm">

        {{-- ===== Thanh 1: tên website (trang chủ) + tìm kiếm + tài khoản ===== --}}
        <nav class="top-bar navbar navbar-dark bg-dark py-2">
            <div class="container d-flex flex-wrap align-items-center">

                <a class="navbar-brand fw-bold order-1 me-2" href="{{ route('home') }}">
                    <i class="bi bi-book-half text-warning"></i> {{ config('app.name') }}
                </a>

                {{-- Ô tìm kiếm: điện thoại nằm dưới hàng đầu, máy tính nằm giữa --}}
                <form class="top-search order-3 order-lg-2 d-flex mt-2 mt-lg-0 mx-lg-4" method="GET"
                    action="{{ route('search') }}">
                    <input class="form-control form-control-sm me-2" type="search" name="q" value="{{ request('q') }}"
                        placeholder="Tìm truyện, tên khác, tác giả..." maxlength="100">
                    <button class="btn btn-warning btn-sm" type="submit" aria-label="Tìm kiếm"><i
                            class="bi bi-search"></i></button>
                </form>

                {{-- Tài khoản + nút mũi tên mở menu (điện thoại) --}}
                <div class="order-2 order-lg-3 ms-auto d-flex align-items-center gap-2">
                    {{-- Chọn chế độ giao diện: Sáng / Tối / Theo hệ thống --}}
                    <div class="dropdown">
                        <button type="button" id="themeToggle" class="btn btn-sm btn-outline-light"
                            data-bs-toggle="dropdown" aria-expanded="false" title="Chọn giao diện"
                            aria-label="Chọn giao diện">
                            <i id="themeIcon" class="bi bi-circle-half"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="themeMenu">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                                    data-theme-choice="light">
                                    <i class="bi bi-sun-fill"></i> Sáng <i
                                        class="bi bi-check2 ms-auto theme-check d-none"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                                    data-theme-choice="dark">
                                    <i class="bi bi-moon-stars-fill"></i> Tối <i
                                        class="bi bi-check2 ms-auto theme-check d-none"></i>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                                    data-theme-choice="auto">
                                    <i class="bi bi-circle-half"></i> Theo hệ thống <i
                                        class="bi bi-check2 ms-auto theme-check d-none"></i>
                                </button>
                            </li>
                        </ul>
                    </div>

                    @guest
                    @if (Route::has('login'))
                    <a class="btn btn-sm btn-outline-light" href="{{ route('login') }}">
                        <i class="bi bi-box-arrow-in-right"></i> <span class="d-none d-sm-inline">Đăng nhập</span>
                    </a>
                    @endif
                    @if (Route::has('register'))
                    <a class="btn btn-sm btn-warning d-none d-sm-inline-block" href="{{ route('register') }}">Đăng
                        ký</a>
                    @endif
                    @else
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            @if (auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" alt="" width="20" height="20" class="rounded-circle"
                                referrerpolicy="no-referrer">
                            @else
                            <i class="bi bi-person-circle"></i>
                            @endif
                            <span class="d-none d-sm-inline">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <h6 class="dropdown-header text-truncate" style="max-width:240px">
                                    {{ auth()->user()->name }}
                                    <span class="d-block fw-normal text-muted small text-truncate">{{
                                        auth()->user()->email }}</span>
                                </h6>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                                    href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person-gear"></i> Hồ sơ của tôi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('follows.*') ? 'active' : '' }}"
                                    href="{{ route('follows.index') }}">
                                    <i class="bi bi-heart"></i> Truyện theo dõi
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ request()->routeIs('history.*') ? 'active' : '' }}"
                                    href="{{ route('history.index') }}">
                                    <i class="bi bi-clock-history"></i> Lịch sử đọc
                                </a>
                            </li>
                            @if (auth()->user()->role === 'admin')
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i
                                        class="bi bi-speedometer2"></i> Trang quản trị</a></li>
                            @endif
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i>
                                        Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                    @endguest

                    <button class="menu-toggle btn btn-sm btn-outline-light d-lg-none" type="button"
                        data-bs-toggle="collapse" data-bs-target="#subMenu" aria-controls="subMenu"
                        aria-expanded="false" aria-label="Mở menu">
                        <i class="bi bi-chevron-down"></i>
                    </button>
                </div>
            </div>
        </nav>

        {{-- ===== Thanh 2: các mục điều hướng (điện thoại: ẩn, bấm mũi tên để trượt xuống) ===== --}}
        <div class="collapse d-lg-block" id="subMenu">
            <nav class="subnav bg-body border-bottom" aria-label="Menu chính">
                <div class="container">
                    <ul class="nav flex-column flex-lg-row flex-lg-wrap">
                        <li class="nav-item">
                            <a class="nav-link {{ $onIndex && !$qStatus && !$qSort ? 'active' : '' }}"
                                href="{{ route('comics.index') }}">
                                <i class="bi bi-collection"></i> Tất cả truyện
                            </a>
                        </li>

                        {{-- Thể loại: rê chuột (máy tính) hoặc bấm (điện thoại) để xem danh sách --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                                href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static"
                                aria-expanded="false">
                                <i class="bi bi-tags"></i> Thể loại
                            </a>
                            <div class="dropdown-menu nav-categories shadow">
                                <a class="dropdown-item fw-semibold text-primary cat-all"
                                    href="{{ route('categories.index') }}">
                                    <i class="bi bi-grid"></i> Tất cả thể loại
                                </a>
                                @forelse (\App\Support\VietnameseAlphabet::group($navCategories) as $letter => $items)
                                <div class="cat-group">
                                    <div class="cat-letter">{{ $letter }}</div>
                                    @foreach ($items as $navCategory)
                                    <a class="dropdown-item rounded"
                                        href="{{ route('categories.show', $navCategory) }}">
                                        {{ $navCategory->name }}
                                    </a>
                                    @endforeach
                                </div>
                                @empty
                                <span class="dropdown-item-text text-muted">Chưa có thể loại nào.</span>
                                @endforelse
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ $onIndex && $qSort === 'views' && !$qStatus ? 'active' : '' }}"
                                href="{{ route('comics.index', ['sort' => 'views']) }}">
                                <i class="bi bi-fire"></i> Xem nhiều
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $onIndex && $qStatus === 'ongoing' ? 'active' : '' }}"
                                href="{{ route('comics.index', ['status' => 'ongoing']) }}">
                                <i class="bi bi-arrow-repeat"></i> Đang tiến hành
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $onIndex && $qStatus === 'completed' ? 'active' : '' }}"
                                href="{{ route('comics.index', ['status' => 'completed']) }}">
                                <i class="bi bi-check-circle"></i> Hoàn thành
                            </a>
                        </li>


                        {{-- Điện thoại nhỏ: nút Đăng ký ở thanh trên bị ẩn nên đưa vào menu --}}
                        @guest
                        @if (Route::has('register'))
                        <li class="nav-item d-sm-none">
                            <a class="nav-link" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Đăng
                                ký</a>
                        </li>
                        @endif
                        @endguest
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    <main class="@yield('main_class', 'container py-4') flex-grow-1">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="bg-dark text-secondary py-3 mt-auto">
        <div class="container text-center small">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Đọc truyện tranh online.
        </div>
    </footer>

    <button type="button" id="backToTop" class="back-to-top btn btn-primary rounded-circle shadow p-0"
        title="Lên đầu trang" aria-label="Lên đầu trang">
        <i class="bi bi-arrow-up fs-5"></i>
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
        var header = document.getElementById('siteHeader');
        if (!header) return;

        var lastY = window.pageYOffset;
        var ticking = false;

        // Không ẩn khi đang mở menu/ danh sách xổ xuống hoặc đang gõ trong ô tìm kiếm
        function keepVisible() {
            if (header.querySelector('.dropdown-menu.show, .collapse.show')) return true;
            var el = document.activeElement;
            return !!(el && header.contains(el) && /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName));
        }

        function update() {
            var y = Math.max(window.pageYOffset, 0);
            var delta = y - lastY;

            if (y <= header.offsetHeight) {
                header.classList.remove('header-hidden');          // gần đầu trang: luôn hiện
            } else if (Math.abs(delta) > 6) {
                if (delta > 0 && !keepVisible()) {
                    header.classList.add('header-hidden');         // cuộn xuống: ẩn
                } else if (delta < 0) {
                    header.classList.remove('header-hidden');      // cuộn lên: hiện
                }
            }

            if (Math.abs(delta) > 6) lastY = y;
            ticking = false;
        }

        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(update);
                ticking = true;
            }
        }, { passive: true });

        // Khi bấm vào ô tìm kiếm hoặc mở menu thì đảm bảo thanh đang hiện
        header.addEventListener('focusin', function () { header.classList.remove('header-hidden'); });
    })();
    </script>
    <script>
        // Chọn chế độ giao diện: sáng / tối / theo hệ thống, ghi nhớ lựa chọn
    (function () {
        var root = document.documentElement;
        var menu = document.getElementById('themeMenu');
        var icon = document.getElementById('themeIcon');
        if (!menu || !icon) return;

        var mq = window.matchMedia('(prefers-color-scheme: dark)');
        var icons = { light: 'bi-sun-fill', dark: 'bi-moon-stars-fill', auto: 'bi-circle-half' };

        function getPref() {
            try {
                var saved = localStorage.getItem('theme');
                return (saved === 'light' || saved === 'dark') ? saved : 'auto';
            } catch (e) { return 'auto'; }
        }

        function apply(pref) {
            root.setAttribute('data-bs-theme', pref === 'auto' ? (mq.matches ? 'dark' : 'light') : pref);
            icon.className = 'bi ' + icons[pref];
            menu.querySelectorAll('[data-theme-choice]').forEach(function (item) {
                var on = item.getAttribute('data-theme-choice') === pref;
                item.querySelector('.theme-check').classList.toggle('d-none', !on);
                item.classList.toggle('fw-semibold', on);
            });
        }

        menu.addEventListener('click', function (e) {
            var item = e.target.closest('[data-theme-choice]');
            if (!item) return;
            var pref = item.getAttribute('data-theme-choice');
            try {
                if (pref === 'auto') localStorage.removeItem('theme');
                else localStorage.setItem('theme', pref);
            } catch (err) {}
            apply(pref);
        });

        // Đang ở chế độ "Theo hệ thống": đổi theo khi thiết bị đổi sáng/tối
        var onSystemChange = function () { if (getPref() === 'auto') apply('auto'); };
        if (mq.addEventListener) mq.addEventListener('change', onSystemChange);
        else if (mq.addListener) mq.addListener(onSystemChange);

        apply(getPref());
    })();

    // Nút mũi tên: hiện khi đã cuộn xuống, bấm để trượt lên đầu trang
    (function () {
        var btn = document.getElementById('backToTop');
        if (!btn) return;

        function toggle() { btn.classList.toggle('show', window.pageYOffset > 300); }
        window.addEventListener('scroll', toggle, { passive: true });
        toggle();

        btn.addEventListener('click', function () {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
        });
    })();
    </script>
    @stack('scripts')
</body>

</html>