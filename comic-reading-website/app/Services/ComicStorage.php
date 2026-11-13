<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Comic;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Quy ước lưu trữ ảnh (ổ đĩa "public" = storage/app/public):
 *
 *   comics/{slug-truyen}/{slug-truyen}-cover.jpg                    ảnh bìa
 *   comics/{slug-truyen}/chap-{so}/{slug-truyen}-chap-{so}-001.jpg  các trang của chương
 *
 * Số chương lẻ 4.5 được viết là "4-5".
 */
class ComicStorage
{
    public function disk(): Filesystem
    {
        return Storage::disk('public');
    }

    // ------------------------------------------------------------------
    // Quy ước tên
    // ------------------------------------------------------------------

    public function numberLabel($number): string
    {
        $text = rtrim(rtrim(number_format((float) $number, 1, '.', ''), '0'), '.');

        return str_replace('.', '-', $text === '' ? '0' : $text);
    }

    public function comicDir(Comic $comic): string
    {
        return 'comics/' . $comic->slug;
    }

    public function chapterDir(Comic $comic, Chapter $chapter): string
    {
        return $this->comicDir($comic) . '/chap-' . $this->numberLabel($chapter->number);
    }

    public function baseName(Comic $comic, Chapter $chapter): string
    {
        return $comic->slug . '-chap-' . $this->numberLabel($chapter->number);
    }

    public function extensionOf(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'jpg'));

        return $ext === 'jpeg' ? 'jpg' : $ext;
    }

    /** Tên chưa bị trùng trong thư mục: ten, ten-2, ten-3... */
    public function uniqueName(string $dir, string $base, string $ext): string
    {
        $name = $base;
        $i = 2;

        while ($this->disk()->exists("{$dir}/{$name}.{$ext}")) {
            $name = $base . '-' . $i++;
        }

        return $name;
    }

    // ------------------------------------------------------------------
    // Lưu file tải lên
    // ------------------------------------------------------------------

    /** Lưu 1 trang ảnh của chương, trả về đường dẫn tương đối */
    public function storeUploaded(Chapter $chapter, UploadedFile $file, int $position): string
    {
        $chapter->loadMissing('comic');
        $comic = $chapter->comic;

        $dir  = $this->chapterDir($comic, $chapter);
        $ext  = $this->extensionOf($file);
        $name = $this->uniqueName($dir, sprintf('%s-%03d', $this->baseName($comic, $chapter), $position), $ext);

        $file->storeAs($dir, "{$name}.{$ext}", 'public');

        return "{$dir}/{$name}.{$ext}";
    }

    public function storeCover(Comic $comic, UploadedFile $file): string
    {
        $dir  = $this->comicDir($comic);
        $ext  = $this->extensionOf($file);
        $name = "{$comic->slug}-cover.{$ext}";

        $file->storeAs($dir, $name, 'public');

        return "{$dir}/{$name}";
    }

    public function deleteCover(Comic $comic): void
    {
        if ($comic->cover && $this->disk()->exists($comic->cover)) {
            $this->disk()->delete($comic->cover);
            $this->deleteDirIfEmpty(dirname($comic->cover));
        }
    }

    // ------------------------------------------------------------------
    // Đổi tên hàng loạt / chuẩn hóa
    // ------------------------------------------------------------------

    /**
     * Chuyển toàn bộ ảnh của chương vào đúng thư mục và đặt lại tên theo thứ tự trang:
     * {slug}-chap-{so}-001, 002, ... Trả về số ảnh đã xử lý.
     */
    public function renumberChapter(Chapter $chapter, array &$oldDirs = []): int
    {
        $chapter->loadMissing('comic');
        $comic = $chapter->comic;

        $dir   = $this->chapterDir($comic, $chapter);
        $base  = $this->baseName($comic, $chapter);
        $disk  = $this->disk();
        $staged = [];

        // Bước 1: chuyển tất cả sang tên tạm trong thư mục đích (tránh trùng tên khi đổi chỗ)
        foreach ($chapter->images()->get() as $image) {
            if (! $disk->exists($image->image_path)) {
                continue;
            }

            $ext = strtolower(pathinfo($image->image_path, PATHINFO_EXTENSION)) ?: 'jpg';
            $tmp = "{$dir}/__tmp_{$image->id}.{$ext}";

            $oldDirs[] = dirname($image->image_path);
            $disk->move($image->image_path, $tmp);

            $staged[] = [$image, $tmp, $ext];
        }

        // Bước 2: đổi sang tên chính thức theo thứ tự hiện tại
        foreach ($staged as $index => [$image, $tmp, $ext]) {
            $final = sprintf('%s/%s-%03d.%s', $dir, $base, $index + 1, $ext);

            $disk->move($tmp, $final);
            $image->update(['image_path' => $final, 'order' => $index + 1]);
        }

        return count($staged);
    }

    /**
     * Chuẩn hóa toàn bộ truyện: ảnh bìa + mọi chương vào thư mục theo tên truyện.
     * Dùng cho ảnh cũ (tên ngẫu nhiên, nhập từ Excel...) hoặc sau khi đổi slug truyện.
     */
    public function organizeComic(Comic $comic): array
    {
        $oldDirs = [];
        $chapters = 0;
        $images = 0;

        foreach ($comic->chapters()->reorder('number')->get() as $chapter) {
            $chapter->setRelation('comic', $comic);
            $images += $this->renumberChapter($chapter, $oldDirs);
            $chapters++;
        }

        // Ảnh bìa
        if ($comic->cover && $this->disk()->exists($comic->cover)) {
            $ext = strtolower(pathinfo($comic->cover, PATHINFO_EXTENSION)) ?: 'jpg';
            $target = $this->comicDir($comic) . "/{$comic->slug}-cover.{$ext}";

            if ($comic->cover !== $target) {
                if ($this->disk()->exists($target)) {
                    $this->disk()->delete($target);
                }
                $oldDirs[] = dirname($comic->cover);
                $this->disk()->move($comic->cover, $target);
                $comic->update(['cover' => $target]);
            }
        }

        // Dọn các thư mục cũ đã trống
        foreach (array_unique($oldDirs) as $oldDir) {
            $this->deleteDirIfEmpty($oldDir);
        }

        return ['chapters' => $chapters, 'images' => $images];
    }

    // ------------------------------------------------------------------
    // Xóa
    // ------------------------------------------------------------------

    public function deleteChapterFiles(Chapter $chapter): void
    {
        $dirs = [];

        foreach ($chapter->images()->get() as $image) {
            if ($this->disk()->exists($image->image_path)) {
                $this->disk()->delete($image->image_path);
            }
            $dirs[] = dirname($image->image_path);
        }

        foreach (array_unique($dirs) as $dir) {
            $this->deleteDirIfEmpty($dir);
        }
    }

    /** Xóa thư mục nếu đã trống, rồi lần lượt xóa các thư mục cha trống (dừng ở comics/ hoặc covers/) */
    public function deleteDirIfEmpty(string $dir): void
    {
        $disk = $this->disk();

        while ($dir !== '' && $dir !== '.' && ! in_array($dir, ['comics', 'covers'], true)) {
            if (! $disk->exists($dir) || count($disk->allFiles($dir)) > 0) {
                break;
            }

            $disk->deleteDirectory($dir);
            $dir = dirname($dir);
        }
    }
}
