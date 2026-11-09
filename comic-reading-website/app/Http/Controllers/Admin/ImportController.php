<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class ImportController extends Controller
{
    // GET admin/imports
    public function index()
    {
        return view('admin.imports.index', [
            'types' => ExcelImportService::definitions(),
        ]);
    }

    // POST admin/imports/{type}
    public function store(Request $request, string $type, ExcelImportService $importer)
    {
        abort_unless(array_key_exists($type, ExcelImportService::definitions()), 404);

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                        $fail('File phải có định dạng .xlsx, .xls hoặc .csv.');
                    }
                },
            ],
        ], [
            'file.required' => 'Vui lòng chọn file Excel.',
            'file.max'      => 'File quá lớn (tối đa 10MB).',
        ]);

        $backUrl = $this->returnUrl($request);

        // Chép file tạm có đuôi đúng để thư viện nhận diện định dạng chính xác
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'import_' . Str::random(16) . '.' . $ext;
        copy($request->file('file')->getRealPath(), $tmp);

        try {
            @set_time_limit(300);
            $result = $importer->import($type, $tmp);
        } catch (InvalidArgumentException $e) {
            return redirect($backUrl)->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect($backUrl)
                ->with('error', 'Không đọc được file: ' . Str::limit($e->getMessage(), 200));
        } finally {
            @unlink($tmp);
        }

        $result['errors']   = array_slice($result['errors'], 0, 200);
        $result['warnings'] = array_slice($result['warnings'], 0, 200);

        return redirect($backUrl)->with('import_result', ['type' => $type] + $result);
    }

    // GET admin/imports/{type}/template
    public function template(string $type, ExcelImportService $importer)
    {
        abort_unless(array_key_exists($type, ExcelImportService::definitions()), 404);

        $writer = new Xlsx($importer->template($type));

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            "mau-{$type}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * Trang sẽ quay lại sau khi nhập. Chỉ cho phép các trang trong danh sách này
     * (tránh bị lợi dụng để chuyển hướng ra ngoài).
     */
    private function returnUrl(Request $request): string
    {
        return match ($request->input('return_to')) {
            'categories' => route('admin.categories.index'),
            'authors'    => route('admin.authors.index'),
            'comics'     => route('admin.comics.index'),
            default      => route('admin.imports.index'),
        };
    }
}
