@extends('layouts.admin')

@section('title', 'Sửa chương')

@section('content')
<a href="{{ route('admin.comics.chapters.index', $chapter->comic) }}" class="small text-decoration-none"><i
        class="bi bi-arrow-left"></i> Danh sách chương</a>
<h4 class="fw-bold mb-3">Sửa chương {{ (float) $chapter->number }} - {{ $chapter->comic->title }}</h4>

{{-- Thông tin chương + thêm ảnh --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chapters.update', $chapter) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Số chương <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" name="number"
                        value="{{ old('number', (float) $chapter->number) }}" required
                        class="form-control @error('number') is-invalid @enderror">
                    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Đổi số chương sẽ đổi tên thư mục và tên ảnh theo.</div>
                </div>

                <div class="col-md-9">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" value="{{ old('title', $chapter->title) }}" maxlength="255"
                        class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Thêm ảnh mới (nối tiếp vào cuối chương)</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                        class="form-control @error('images.*') is-invalid @enderror">
                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        Ảnh được đặt tên dạng
                        <code>{{ $chapter->comic->slug }}-chap-{{ str_replace('.', '-', (string) (float) $chapter->number) }}-001.jpg</code>
                        và lưu trong thư mục tên truyện.
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Cập nhật</button>
                <a href="{{ route('admin.comics.chapters.index', $chapter->comic) }}"
                    class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

{{-- Quản lý ảnh (nằm ngoài form chính để tránh lồng form) --}}
<div class="card shadow-sm" id="imageManager">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="fw-semibold"><i class="bi bi-images"></i> Quản lý ảnh ({{ $chapter->images->count() }})</span>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <small class="text-muted"><i class="bi bi-arrows-move"></i> Kéo thả ảnh để đổi vị trí, thứ tự tự động được
                lưu</small>
            <form method="POST" action="{{ route('admin.chapters.images.renumber', $chapter) }}"
                onsubmit="return confirm('Đặt lại tên tất cả ảnh của chương theo thứ tự hiện tại (001, 002, ...)? Các tên bạn đã tự đặt sẽ bị thay.')">
                @csrf
                <button class="btn btn-outline-secondary btn-sm" title="Đặt lại tên ảnh theo thứ tự trang">
                    <i class="bi bi-sort-numeric-down"></i> Đánh số lại tên ảnh
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <div class="small text-muted mb-2">Thư mục: <code>storage/app/public/{{ $folder }}</code></div>
        <div id="saveStatus" class="small mb-2" style="min-height:1.3em"></div>

        @if ($chapter->images->isEmpty())
        <p class="text-muted mb-0">Chương chưa có ảnh.</p>
        @else
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-2" id="imageGrid">
            @foreach ($chapter->images as $image)
            @php
            $fileName = basename($image->image_path);
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            @endphp
            <div class="col image-item" data-id="{{ $image->id }}">
                <div class="card h-100">
                    <div class="img-drag position-relative" style="cursor:grab" title="Kéo để đổi vị trí">
                        <img src="{{ asset('storage/' . $image->image_path) }}?v={{ $image->updated_at?->timestamp }}"
                            class="card-img-top" style="height:150px;object-fit:cover" loading="lazy" draggable="false"
                            alt="{{ $fileName }}">
                        <span class="badge text-bg-dark position-absolute top-0 start-0 m-1 pos-badge">#{{
                            $loop->iteration }}</span>
                    </div>

                    <div class="card-body p-2">
                        <div class="small fw-semibold text-truncate" title="{{ $fileName }}">{{ $fileName }}</div>

                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm px-2 py-0" title="Đổi tên"
                                data-bs-toggle="modal" data-bs-target="#renameModal"
                                data-action="{{ route('admin.chapters.images.rename', [$chapter, $image]) }}"
                                data-name="{{ $baseName }}" data-ext=".{{ $ext }}">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button type="button" class="btn btn-outline-success btn-sm px-2 py-0" title="Thay ảnh khác"
                                data-bs-toggle="modal" data-bs-target="#replaceModal"
                                data-action="{{ route('admin.chapters.images.replace', [$chapter, $image]) }}"
                                data-name="{{ $fileName }}">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>

                            <form method="POST" action="{{ route('admin.chapters.images.rotate', [$chapter, $image]) }}"
                                class="d-inline">
                                @csrf <input type="hidden" name="direction" value="left">
                                <button class="btn btn-outline-secondary btn-sm px-2 py-0" title="Xoay trái 90°"><i
                                        class="bi bi-arrow-counterclockwise"></i></button>
                            </form>

                            <form method="POST" action="{{ route('admin.chapters.images.rotate', [$chapter, $image]) }}"
                                class="d-inline">
                                @csrf <input type="hidden" name="direction" value="right">
                                <button class="btn btn-outline-secondary btn-sm px-2 py-0" title="Xoay phải 90°"><i
                                        class="bi bi-arrow-clockwise"></i></button>
                            </form>

                            <form method="POST"
                                action="{{ route('admin.chapters.images.destroy', [$chapter, $image]) }}"
                                class="d-inline" onsubmit="return confirm('Xóa ảnh này?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm px-2 py-0" title="Xóa ảnh"><i
                                        class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Cửa sổ đổi tên ảnh --}}
<div class="modal fade" id="renameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Đổi tên ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Tên mới</label>
                <div class="input-group">
                    <input type="text" name="name" class="form-control" maxlength="120" required>
                    <span class="input-group-text ext-hint"></span>
                </div>
                <div class="form-text">Tên được chuyển thành không dấu, nối bằng gạch ngang (vd: "Trang bìa" thành
                    trang-bia).</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary">Đổi tên</button>
            </div>
        </form>
    </div>
</div>

{{-- Cửa sổ thay ảnh --}}
<div class="modal fade" id="replaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Thay ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted mb-2">Ảnh mới sẽ thay thế <strong class="replace-name"></strong>, giữ nguyên
                    vị trí và tên file.</p>
                <input type="file" name="image" accept="image/*" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-success">Thay ảnh</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    (function () {
        // ----- Kéo thả đổi vị trí, tự lưu -----
        var grid = document.getElementById('imageGrid');
        var status = document.getElementById('saveStatus');

        if (grid && window.Sortable) {
            var url = @json(route('admin.chapters.images.reorder', $chapter));
            var token = document.querySelector('meta[name="csrf-token"]').content;

            new Sortable(grid, {
                animation: 150,
                draggable: '.image-item',
                handle: '.img-drag',
                ghostClass: 'opacity-50',
                onEnd: function () {
                    var items = Array.from(grid.querySelectorAll('.image-item'));
                    items.forEach(function (el, i) { el.querySelector('.pos-badge').textContent = '#' + (i + 1); });

                    status.className = 'small mb-2 text-muted';
                    status.textContent = 'Đang lưu thứ tự...';

                    fetch(url, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        body: JSON.stringify({ ids: items.map(function (el) { return parseInt(el.dataset.id, 10); }) })
                    }).then(function (r) {
                        if (!r.ok) throw new Error('save failed');
                        status.className = 'small mb-2 text-success';
                        status.textContent = 'Đã lưu thứ tự ảnh.';
                    }).catch(function () {
                        status.className = 'small mb-2 text-danger';
                        status.textContent = 'Không lưu được thứ tự. Hãy tải lại trang và thử lại.';
                    });
                }
            });
        }

        // ----- Cửa sổ đổi tên -----
        var renameModal = document.getElementById('renameModal');
        renameModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            renameModal.querySelector('form').action = btn.dataset.action;
            renameModal.querySelector('input[name="name"]').value = btn.dataset.name;
            renameModal.querySelector('.ext-hint').textContent = btn.dataset.ext;
        });
        renameModal.addEventListener('shown.bs.modal', function () {
            renameModal.querySelector('input[name="name"]').select();
        });

        // ----- Cửa sổ thay ảnh -----
        var replaceModal = document.getElementById('replaceModal');
        replaceModal.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            replaceModal.querySelector('form').action = btn.dataset.action;
            replaceModal.querySelector('.replace-name').textContent = btn.dataset.name;
            replaceModal.querySelector('input[type="file"]').value = '';
        });
    })();
</script>
@endpush