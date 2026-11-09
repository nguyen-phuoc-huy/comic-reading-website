@extends('layouts.admin')

@section('title', 'Nhập dữ liệu từ Excel')

@section('content')
<h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-spreadsheet"></i> Nhập dữ liệu từ Excel</h4>
<p class="text-muted">
    Mỗi loại dữ liệu nhập bằng <strong>một file riêng</strong> (hỗ trợ .xlsx, .xls, .csv). Hệ thống chỉ đọc
    <strong>sheet đầu tiên</strong>, dòng 1 là tên cột. Nên nhập theo thứ tự từ 1 đến 5 vì các bước sau phụ thuộc bước trước.
</p>

{{-- Kết quả lần nhập gần nhất --}}
@php $r = session('import_result'); @endphp
@if ($r)
    <div class="card shadow-sm mb-4 border-{{ count($r['errors']) ? 'warning' : 'success' }}">
        <div class="card-header fw-semibold">
            <i class="bi bi-clipboard-check"></i> Kết quả nhập: {{ $types[$r['type']]['label'] }}
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <span class="badge text-bg-success fs-6">Thêm mới: {{ $r['created'] }}</span>
                <span class="badge text-bg-primary fs-6">Cập nhật: {{ $r['updated'] }}</span>
                <span class="badge text-bg-secondary fs-6">Bỏ qua (đã có): {{ $r['skipped'] }}</span>
                <span class="badge text-bg-{{ count($r['errors']) ? 'danger' : 'light border text-muted' }} fs-6">Lỗi: {{ count($r['errors']) }}</span>
                <span class="badge text-bg-{{ count($r['warnings']) ? 'warning' : 'light border text-muted' }} fs-6">Cảnh báo: {{ count($r['warnings']) }}</span>
            </div>

            @if (count($r['errors']))
                <details class="mt-2" open>
                    <summary class="text-danger fw-semibold">Các dòng bị lỗi (không được nhập)</summary>
                    <ul class="small mb-0 mt-1" style="max-height:220px;overflow:auto">
                        @foreach ($r['errors'] as $msg)<li>{{ $msg }}</li>@endforeach
                    </ul>
                </details>
            @endif

            @if (count($r['warnings']))
                <details class="mt-2">
                    <summary class="text-warning-emphasis fw-semibold">Cảnh báo (dòng vẫn được nhập)</summary>
                    <ul class="small mb-0 mt-1" style="max-height:220px;overflow:auto">
                        @foreach ($r['warnings'] as $msg)<li>{{ $msg }}</li>@endforeach
                    </ul>
                </details>
            @endif
        </div>
    </div>
@endif

@if ($errors->has('file'))
    <div class="alert alert-danger">{{ $errors->first('file') }}</div>
@endif

<div class="row row-cols-1 row-cols-xl-2 g-4">
    @foreach ($types as $key => $type)
        <div class="col">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">
                        <span class="badge text-bg-dark me-1">{{ $loop->iteration }}</span>
                        <i class="bi {{ $type['icon'] }}"></i> {{ $type['label'] }}
                    </span>
                    <a href="{{ route('admin.imports.template', $key) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-download"></i> File mẫu
                    </a>
                </div>
                <div class="card-body d-flex flex-column">
                    <p class="small text-muted">{{ $type['description'] }}</p>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered small mb-0">
                            <thead class="table-light">
                                <tr><th style="width:110px">Cột</th><th style="width:80px">Bắt buộc</th><th>Ý nghĩa</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($type['columns'] as $col => $info)
                                    <tr>
                                        <td><code>{{ $col }}</code></td>
                                        <td>{!! $info['required'] ? '<span class="text-danger fw-semibold">Có</span>' : 'Không' !!}</td>
                                        <td>{{ $info['desc'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('admin.imports.store', $key) }}" enctype="multipart/form-data" class="mt-auto">
                        @csrf
                        <div class="input-group">
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <button class="btn btn-primary"><i class="bi bi-upload"></i> Nhập</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
