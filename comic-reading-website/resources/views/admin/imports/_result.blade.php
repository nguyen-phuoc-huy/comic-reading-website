@php
    $r = session('import_result');
    $importLabel = $r
        ? (\App\Services\ExcelImportService::definitions()[$r['type']]['label'] ?? $r['type'])
        : null;
@endphp

@if ($r)
    <div class="card shadow-sm mb-4 border-{{ count($r['errors']) ? 'warning' : 'success' }}">
        <div class="card-header fw-semibold">
            <i class="bi bi-clipboard-check"></i> Kết quả nhập Excel: {{ $importLabel }}
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
