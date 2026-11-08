@extends('layouts.admin')

@section('title', 'Thêm thể loại')

@section('content')
<h4 class="fw-bold mb-3">Thêm thể loại</h4>

<div class="card shadow-sm" style="max-width:640px">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Tên thể loại <span class="text-danger">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Lưu</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Hủy</a>
        </form>
    </div>
</div>
@endsection
