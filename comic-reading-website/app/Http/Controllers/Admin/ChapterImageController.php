<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterImage;
use App\Services\ComicStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChapterImageController extends Controller
{
    // PATCH admin/chapters/{chapter}/images/reorder  (kéo thả, gọi bằng AJAX)
    public function reorder(Request $request, Chapter $chapter)
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ])['ids'];

        $owned = ChapterImage::where('chapter_id', $chapter->id)->pluck('id')->all();
        abort_if(array_diff($ids, $owned), 422, 'Có ảnh không thuộc chương này.');

        DB::transaction(function () use ($ids, $chapter) {
            foreach (array_values($ids) as $position => $id) {
                ChapterImage::where('id', $id)
                    ->where('chapter_id', $chapter->id)
                    ->update(['order' => $position + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    // PUT admin/chapters/{chapter}/images/{image}/rename
    public function rename(Request $request, Chapter $chapter, ChapterImage $image, ComicStorage $storage)
    {
        $this->ensureOwned($chapter, $image);

        $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ], ['name.required' => 'Vui lòng nhập tên ảnh.']);

        // Bỏ đuôi file nếu người dùng gõ kèm (.jpg...), rồi chuyển thành tên không dấu, nối bằng gạch ngang
        $raw  = preg_replace('/\.(jpe?g|png|webp|gif)$/i', '', trim($request->input('name')));
        $base = Str::slug($raw);

        if ($base === '') {
            return back()->with('error', 'Tên ảnh không hợp lệ.');
        }

        $ext = pathinfo($image->image_path, PATHINFO_EXTENSION);
        $new = dirname($image->image_path) . '/' . $base . '.' . $ext;

        if ($new === $image->image_path) {
            return back();
        }

        $disk = $storage->disk();

        if (! $disk->exists($image->image_path)) {
            return back()->with('error', 'Không tìm thấy file ảnh trên ổ đĩa.');
        }
        if ($disk->exists($new)) {
            return back()->with('error', 'Đã có ảnh khác trùng tên này trong thư mục.');
        }

        $disk->move($image->image_path, $new);
        $image->update(['image_path' => $new]);

        return back()->with('success', 'Đã đổi tên ảnh thành ' . basename($new) . '.');
    }

    // POST admin/chapters/{chapter}/images/{image}/replace
    public function replace(Request $request, Chapter $chapter, ChapterImage $image, ComicStorage $storage)
    {
        $this->ensureOwned($chapter, $image);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'image.required' => 'Vui lòng chọn ảnh mới.',
            'image.image'    => 'File phải là ảnh.',
            'image.mimes'    => 'Ảnh phải có định dạng jpg, png hoặc webp.',
            'image.max'      => 'Ảnh quá lớn (tối đa 5MB).',
        ]);

        $disk = $storage->disk();
        $file = $request->file('image');
        $dir  = dirname($image->image_path);
        $base = pathinfo($image->image_path, PATHINFO_FILENAME);
        $ext  = $storage->extensionOf($file);
        $new  = "{$dir}/{$base}.{$ext}";

        $file->storeAs($dir, "{$base}.{$ext}", 'public');

        if ($new !== $image->image_path && $disk->exists($image->image_path)) {
            $disk->delete($image->image_path); // đổi đuôi file (vd .png -> .jpg)
        }

        $image->update(['image_path' => $new]);
        $image->touch();

        return back()->with('success', 'Đã thay ảnh.');
    }

    // POST admin/chapters/{chapter}/images/{image}/rotate
    public function rotate(Request $request, Chapter $chapter, ChapterImage $image, ComicStorage $storage)
    {
        $this->ensureOwned($chapter, $image);

        $direction = $request->validate(['direction' => ['required', 'in:left,right']])['direction'];

        $path = $storage->disk()->path($image->image_path);
        if (! is_file($path)) {
            return back()->with('error', 'Không tìm thấy file ảnh trên ổ đĩa.');
        }
        if (! function_exists('imagerotate')) {
            return back()->with('error', 'Máy chủ chưa bật thư viện GD nên không xoay được ảnh.');
        }

        @ini_set('memory_limit', '512M');

        $type = @getimagesize($path)[2] ?? null;

        [$load, $save] = match ($type) {
            IMAGETYPE_JPEG => ['imagecreatefromjpeg', fn ($img) => imagejpeg($img, $path, 92)],
            IMAGETYPE_PNG  => ['imagecreatefrompng', fn ($img) => imagepng($img, $path, 6)],
            IMAGETYPE_WEBP => ['imagecreatefromwebp', fn ($img) => imagewebp($img, $path, 90)],
            default        => [null, null],
        };

        if (! $load || ! function_exists($load)) {
            return back()->with('error', 'Định dạng ảnh này chưa hỗ trợ xoay.');
        }

        $source = @$load($path);
        if (! $source) {
            return back()->with('error', 'Không đọc được ảnh để xoay.');
        }

        // Giữ nền trong suốt với PNG / WebP
        imagealphablending($source, false);
        imagesavealpha($source, true);

        $rotated = imagerotate($source, $direction === 'left' ? 90 : -90, 0);
        imagedestroy($source);

        if (! $rotated) {
            return back()->with('error', 'Không xoay được ảnh.');
        }

        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);
        $save($rotated);
        imagedestroy($rotated);

        $image->touch();

        return back()->with('success', 'Đã xoay ảnh.');
    }

    // POST admin/chapters/{chapter}/images/renumber
    public function renumber(Chapter $chapter, ComicStorage $storage)
    {
        $count = $storage->renumberChapter($chapter);

        return back()->with('success', "Đã đặt lại tên {$count} ảnh theo thứ tự trang.");
    }

    private function ensureOwned(Chapter $chapter, ChapterImage $image): void
    {
        abort_unless($image->chapter_id === $chapter->id, 404);
    }
}
