<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Tải một chương truyện về dưới dạng file PDF (mỗi trang ảnh là một trang PDF).
 * Cần thư viện: composer require setasign/fpdf
 */
class ChapterDownloadController extends Controller
{
    // GET /truyen/{comic:slug}/chap-{chapter:number}/tai-pdf
    public function __invoke(Comic $comic, Chapter $chapter)
    {
        if (! class_exists(\FPDF::class)) {
            return back()->with('error', 'Chưa cài thư viện tạo PDF. Hãy chạy: composer require setasign/fpdf');
        }

        // Chương có nhiều ảnh nên cần thêm thời gian và bộ nhớ
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $disk  = Storage::disk('public');
        $files = $chapter->images()->get()->filter(fn($image) => $disk->exists($image->image_path));

        if ($files->isEmpty()) {
            return back()->with('error', 'Chương này chưa có ảnh để tải.');
        }

        // File PDF được lưu đệm lại; đổi ảnh của chương thì chữ ký đổi và PDF được tạo lại
        $signature = md5($chapter->id . '|' . $files->map(
            fn($i) => $i->id . ':' . $i->image_path . ':' . $i->order . ':' . $i->updated_at?->timestamp
        )->implode(','));

        $dir = storage_path('app/pdf-cache');
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $path = "{$dir}/chapter-{$chapter->id}-{$signature}.pdf";

        if (! is_file($path)) {
            foreach (glob("{$dir}/chapter-{$chapter->id}-*.pdf") ?: [] as $old) {
                @unlink($old); // xóa bản PDF cũ của chương này
            }

            try {
                $this->buildPdf($comic, $chapter, $files, $disk, $path);
            } catch (Throwable $e) {
                report($e);
                @unlink($path);

                return back()->with('error', 'Không tạo được file PDF: ' . Str::limit($e->getMessage(), 150));
            }
        }

        $filename = (Str::slug($comic->title) ?: 'truyen') . '-chap-' . (float) $chapter->number . '.pdf';

        return response()->download($path, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function buildPdf(Comic $comic, Chapter $chapter, $files, $disk, string $path): void
    {
        $pdf = new \FPDF('P', 'mm');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTitle($comic->title . ' - Chap ' . (float) $chapter->number, true);
        $pdf->SetCreator(config('app.name'), true);

        foreach ($files as $image) {
            $prepared = $this->prepareImage($disk->path($image->image_path));
            if (! $prepared) {
                continue; // bỏ qua ảnh hỏng hoặc định dạng không đọc được
            }

            [$file, $width, $height, $isTemp] = $prepared;

            // Trang rộng 210mm, cao theo đúng tỉ lệ ảnh (không cắt, không méo)
            $pageW = 210.0;
            $pageH = $pageW * $height / $width;
            if ($pageH > 5000) { // giới hạn kích thước trang của PDF
                $scale = 5000 / $pageH;
                $pageW *= $scale;
                $pageH = 5000;
            }

            $pdf->AddPage($pageW > $pageH ? 'L' : 'P', [$pageW, $pageH]);
            $pdf->Image($file, 0, 0, $pageW, $pageH, 'JPG');

            if ($isTemp) {
                @unlink($file);
            }
        }

        if ($pdf->PageNo() === 0) {
            throw new RuntimeException('Không đọc được ảnh nào của chương này.');
        }

        $pdf->Output('F', $path);
    }

    /**
     * Trả về [đường dẫn JPEG, rộng, cao, là-file-tạm]. FPDF chỉ nhúng trực tiếp được JPEG,
     * nên PNG / WebP / GIF được đổi sang JPEG (nền trắng) bằng GD.
     */
    private function prepareImage(string $absolutePath): ?array
    {
        $info = @getimagesize($absolutePath);
        if (! $info || ! $info[0] || ! $info[1]) {
            return null;
        }

        [$width, $height, $type] = $info;

        if ($type === IMAGETYPE_JPEG) {
            return [$absolutePath, $width, $height, false];
        }

        $source = match ($type) {
            IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($absolutePath) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            IMAGETYPE_GIF  => function_exists('imagecreatefromgif') ? @imagecreatefromgif($absolutePath) : false,
            default        => false,
        };

        if (! $source) {
            return null;
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        $tmp = tempnam(sys_get_temp_dir(), 'pg');
        $jpg = $tmp . '.jpg';
        rename($tmp, $jpg);
        imagejpeg($canvas, $jpg, 92);

        imagedestroy($source);
        imagedestroy($canvas);

        return [$jpg, $width, $height, true];
    }
}
