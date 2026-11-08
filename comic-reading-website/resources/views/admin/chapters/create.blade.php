@extends('layouts.admin')

@section('title', 'Thêm chương')

@section('content')
<a href="{{ route('admin.comics.chapters.index', $comic) }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Danh sách chương</a>
<h4 class="fw-bold mb-3">Thêm chương cho: {{ $comic->title }}</h4>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.comics.chapters.store', $comic) }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Số chương <span class="text-danger">*</span></label>
                    <input type="number" step="0.1" min="0" name="number" value="{{ old('number', $nextNumber) }}" required
                           class="form-control @error('number') is-invalid @enderror">
                    @error('number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Có thể nhập số lẻ, ví dụ 10.5</div>
                </div>

                <div class="col-md-9">
                    <label class="form-label">Tiêu đề (không bắt buộc)</label>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                           class="form-control @error('title') is-invalid @enderror">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Ảnh các trang <span class="text-danger">*</span></label>
                    <input type="file" id="images" name="images[]" multiple required accept="image/*"
                           class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror">
                    @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">
                        Chọn nhiều ảnh cùng lúc (jpg, png, webp, tối đa 5MB/ảnh). Thứ tự trang được sắp xếp theo tên file
                        (001.jpg, 002.jpg, ...). <span id="image-count" class="fw-semibold"></span>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Đăng chương</button>
                <a href="{{ route('admin.comics.chapters.index', $comic) }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('images').addEventListener('change', function () {
        document.getElementById('image-count').textContent =
            this.files.length ? 'Đã chọn ' + this.files.length + ' ảnh.' : '';
    });
</script>
@endpush
