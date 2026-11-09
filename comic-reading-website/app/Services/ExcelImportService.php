<?php

namespace App\Services;

use App\Models\Author;
use App\Models\Category;
use App\Models\Chapter;
use App\Models\ChapterImage;
use App\Models\Comic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Throwable;

/**
 * Nhập dữ liệu từ file Excel/CSV. Mỗi loại dữ liệu là một file riêng,
 * hệ thống chỉ đọc SHEET ĐẦU TIÊN, dòng đầu tiên là tên cột.
 */
class ExcelImportService
{
    /** Cache tìm truyện theo tên để import nhiều dòng nhanh hơn */
    private array $comicCache = [];

    /** Thứ tự ảnh kế tiếp của từng chương */
    private array $orderCursor = [];

    /**
     * Khai báo các loại file nhập: dùng cho cả trang giao diện lẫn file mẫu.
     */
    public static function definitions(): array
    {
        return [
            'authors' => [
                'label'       => 'Tác giả',
                'icon'        => 'bi-person-lines-fill',
                'description' => 'Mỗi dòng là một tác giả. Tác giả trùng tên đã có sẽ được bỏ qua.',
                'columns'     => [
                    'name' => ['required' => true, 'desc' => 'Tên tác giả', 'example' => 'Hoàng Minh Khoa'],
                ],
            ],
            'categories' => [
                'label'       => 'Thể loại',
                'icon'        => 'bi-tags',
                'description' => 'Thể loại trùng tên đã có sẽ được cập nhật mô tả.',
                'columns'     => [
                    'name'        => ['required' => true, 'desc' => 'Tên thể loại', 'example' => 'Hành động'],
                    'description' => ['required' => false, 'desc' => 'Mô tả ngắn', 'example' => 'Những cuộc chiến nảy lửa.'],
                ],
            ],
            'comics' => [
                'label'       => 'Truyện',
                'icon'        => 'bi-journal-richtext',
                'description' => 'Truyện được nhận diện theo tên: trùng tên thì cập nhật, chưa có thì tạo mới. '
                    . 'Tác giả và thể loại phải được nhập trước. Chỉ những cột có dữ liệu mới ghi đè.',
                'columns'     => [
                    'title'       => ['required' => true, 'desc' => 'Tên truyện', 'example' => 'Kiếm Sĩ Vô Danh'],
                    'author'      => ['required' => false, 'desc' => 'Tên tác giả (phải khớp với tác giả đã nhập)', 'example' => 'Hoàng Minh Khoa'],
                    'other_names' => ['required' => false, 'desc' => 'Tên khác', 'example' => 'Nameless Swordsman'],
                    'description' => ['required' => false, 'desc' => 'Nội dung / mô tả', 'example' => 'Một kiếm sĩ mất trí nhớ...'],
                    'status'      => ['required' => false, 'desc' => 'ongoing / completed / dropped (hoặc: Đang tiến hành / Hoàn thành / Tạm ngưng)', 'example' => 'ongoing'],
                    'categories'  => ['required' => false, 'desc' => 'Các thể loại, ngăn cách bằng dấu phẩy', 'example' => 'Hành động, Phiêu lưu'],
                    'cover'       => ['required' => false, 'desc' => 'Đường dẫn ảnh bìa trong storage/app/public', 'example' => 'covers/demo/kiem-si-vo-danh.jpg'],
                    'views'       => ['required' => false, 'desc' => 'Lượt xem ban đầu (số)', 'example' => 0],
                ],
            ],
            'chapters' => [
                'label'       => 'Chương',
                'icon'        => 'bi-files',
                'description' => 'Chương được nhận diện theo (truyện + số chương): trùng thì cập nhật, chưa có thì tạo mới. Truyện phải được nhập trước.',
                'columns'     => [
                    'comic'  => ['required' => true, 'desc' => 'Tên truyện (phải khớp truyện đã nhập)', 'example' => 'Kiếm Sĩ Vô Danh'],
                    'number' => ['required' => true, 'desc' => 'Số chương (có thể là số lẻ, ví dụ 4.5)', 'example' => 1],
                    'title'  => ['required' => false, 'desc' => 'Tiêu đề chương', 'example' => 'Lời thề dưới trăng'],
                    'views'  => ['required' => false, 'desc' => 'Lượt xem ban đầu (số)', 'example' => 0],
                ],
            ],
            'chapter-images' => [
                'label'       => 'Ảnh chương',
                'icon'        => 'bi-images',
                'description' => 'Gắn các trang ảnh vào chương. Chương phải được nhập trước, và file ảnh cần được chép sẵn vào storage/app/public theo đúng đường dẫn.',
                'columns'     => [
                    'comic'      => ['required' => true, 'desc' => 'Tên truyện', 'example' => 'Kiếm Sĩ Vô Danh'],
                    'chapter'    => ['required' => true, 'desc' => 'Số chương', 'example' => 1],
                    'image_path' => ['required' => true, 'desc' => 'Đường dẫn ảnh trong storage/app/public', 'example' => 'comics/demo/kiem-si-vo-danh/chap-1/001.jpg'],
                    'order'      => ['required' => false, 'desc' => 'Thứ tự trang (bỏ trống thì tự đánh số theo thứ tự dòng)', 'example' => 1],
                ],
            ],
        ];
    }

    /**
     * Nhập một file. Trả về thống kê: created, updated, skipped, errors, warnings.
     *
     * @throws InvalidArgumentException nếu file sai định dạng (thiếu cột...)
     */
    public function import(string $type, string $path): array
    {
        $definition = self::definitions()[$type]
            ?? throw new InvalidArgumentException('Loại dữ liệu không hợp lệ.');

        [$header, $rows] = $this->readRows($path);

        $missing = [];
        foreach ($definition['columns'] as $key => $info) {
            if ($info['required'] && ! in_array($key, $header, true)) {
                $missing[] = $key;
            }
        }
        if ($missing) {
            throw new InvalidArgumentException(
                'File thiếu cột bắt buộc: ' . implode(', ', $missing)
                . '. Hãy tải "File mẫu" để xem đúng định dạng (dòng 1 là tên cột).'
            );
        }
        if (! $rows) {
            throw new InvalidArgumentException('File không có dòng dữ liệu nào.');
        }

        $handler = match ($type) {
            'authors'        => $this->importAuthor(...),
            'categories'     => $this->importCategory(...),
            'comics'         => $this->importComic(...),
            'chapters'       => $this->importChapter(...),
            'chapter-images' => $this->importChapterImage(...),
        };

        return $this->run($rows, $handler);
    }

    /**
     * Tạo file Excel mẫu (dòng 1 là tên cột, dòng 2 là ví dụ).
     */
    public function template(string $type): Spreadsheet
    {
        $definition = self::definitions()[$type]
            ?? throw new InvalidArgumentException('Loại dữ liệu không hợp lệ.');

        $headers = array_keys($definition['columns']);
        $example = array_map(fn ($c) => $c['example'], array_values($definition['columns']));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($definition['label'], 0, 30));
        $sheet->fromArray([$headers, $example], null, 'A1');

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        foreach (range(1, count($headers)) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        return $spreadsheet;
    }

    // ------------------------------------------------------------------
    // Đọc file & vòng lặp xử lý từng dòng
    // ------------------------------------------------------------------

    private function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0); // chỉ đọc sheet đầu tiên

        // (nullValue, calculateFormulas, formatData, returnCellRef)
        $raw = $sheet->toArray(null, true, false, false);

        if (! $raw) {
            return [[], []];
        }

        $headerRow = array_shift($raw);
        $header = array_map(
            fn ($h) => Str::of((string) $h)->trim()->lower()->replace([' ', '-'], '_')->toString(),
            $headerRow
        );

        $rows = [];
        foreach ($raw as $index => $line) {
            $data = [];
            foreach ($header as $col => $key) {
                if ($key === '') {
                    continue;
                }
                $value = $line[$col] ?? null;
                $data[$key] = is_string($value) ? trim($value) : $value;
            }

            $isBlank = collect($data)->every(fn ($v) => $v === null || $v === '');
            if ($isBlank) {
                continue;
            }

            $rows[] = ['row' => $index + 2, 'data' => $data];
        }

        return [$header, $rows];
    }

    private function run(array $rows, callable $handler): array
    {
        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [], 'warnings' => []];

        foreach ($rows as ['row' => $line, 'data' => $data]) {
            $warn = function (string $message) use (&$result, $line) {
                $result['warnings'][] = "Dòng {$line}: {$message}";
            };

            try {
                $status = $handler($data, $warn);
                $result[$status]++;
            } catch (InvalidArgumentException $e) {
                $result['errors'][] = "Dòng {$line}: " . $e->getMessage();
            } catch (Throwable $e) {
                report($e);
                $result['errors'][] = "Dòng {$line}: Lỗi hệ thống - " . Str::limit($e->getMessage(), 150);
            }
        }

        return $result;
    }

    // ------------------------------------------------------------------
    // Xử lý từng loại dữ liệu. Mỗi hàm trả về: created | updated | skipped
    // ------------------------------------------------------------------

    private function importAuthor(array $d, callable $warn): string
    {
        $name = $this->str($d, 'name') ?? throw new InvalidArgumentException('Thiếu tên tác giả.');

        if (Author::where('name', $name)->exists()) {
            return 'skipped';
        }

        Author::create([
            'name' => $name,
            'slug' => $this->uniqueSlug(Author::class, $name),
        ]);

        return 'created';
    }

    private function importCategory(array $d, callable $warn): string
    {
        $name = $this->str($d, 'name') ?? throw new InvalidArgumentException('Thiếu tên thể loại.');
        $description = $this->str($d, 'description');

        $category = Category::where('name', $name)->first();

        if ($category) {
            if ($description !== null && $description !== $category->description) {
                $category->update(['description' => $description]);
                return 'updated';
            }
            return 'skipped';
        }

        Category::create([
            'name'        => $name,
            'slug'        => $this->uniqueSlug(Category::class, $name),
            'description' => $description,
        ]);

        return 'created';
    }

    private function importComic(array $d, callable $warn): string
    {
        $title = $this->str($d, 'title') ?? throw new InvalidArgumentException('Thiếu tên truyện.');
        $slug = Str::slug($title);
        if ($slug === '') {
            throw new InvalidArgumentException('Tên truyện không hợp lệ.');
        }

        $attrs = [];

        if (($authorName = $this->str($d, 'author')) !== null) {
            $author = Author::where('name', $authorName)->first();
            if ($author) {
                $attrs['author_id'] = $author->id;
            } else {
                $warn("Không tìm thấy tác giả '{$authorName}', để trống tác giả (hãy nhập file tác giả trước).");
            }
        }

        foreach (['other_names', 'description'] as $field) {
            if (($value = $this->str($d, $field)) !== null) {
                $attrs[$field] = $value;
            }
        }

        if (($cover = $this->str($d, 'cover')) !== null) {
            $cover = $this->cleanPath($cover);
            if (! Storage::disk('public')->exists($cover)) {
                $warn("Chưa thấy file ảnh bìa '{$cover}' trong storage/app/public.");
            }
            $attrs['cover'] = $cover;
        }

        if (($status = $this->str($d, 'status')) !== null) {
            $attrs['status'] = $this->mapStatus($status, $warn);
        }

        if (($views = $this->num($d, 'views')) !== null) {
            $attrs['views'] = max(0, (int) $views);
        }

        // Thể loại: chỉ gắn các thể loại đã tồn tại
        $categoryIds = null;
        if (($categoryText = $this->str($d, 'categories')) !== null) {
            $categoryIds = [];
            foreach (preg_split('/[,;|]/', $categoryText) as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $category = Category::where('name', $name)->first();
                if ($category) {
                    $categoryIds[] = $category->id;
                } else {
                    $warn("Không tìm thấy thể loại '{$name}', đã bỏ qua (hãy nhập file thể loại trước).");
                }
            }
        }

        $comic = Comic::withTrashed()->where('slug', $slug)->first();

        if ($comic) {
            $changed = false;

            if ($comic->trashed()) {
                $comic->restore();
                $changed = true;
            }

            $comic->fill($attrs);
            if ($comic->isDirty()) {
                $comic->save();
                $changed = true;
            }

            $status = $changed ? 'updated' : 'skipped';
        } else {
            $comic = Comic::create($attrs + ['title' => $title, 'slug' => $slug]);
            $status = 'created';
        }

        if ($categoryIds !== null) {
            $sync = $comic->categories()->sync($categoryIds);
            if ($status === 'skipped' && (count($sync['attached']) || count($sync['detached']))) {
                $status = 'updated';
            }
        }

        return $status;
    }

    private function importChapter(array $d, callable $warn): string
    {
        $comic = $this->findComic($this->str($d, 'comic'));
        $number = $this->chapterNumber($d['number'] ?? null);

        $attrs = [];
        if (($title = $this->str($d, 'title')) !== null) {
            $attrs['title'] = $title;
        }
        if (($views = $this->num($d, 'views')) !== null) {
            $attrs['views'] = max(0, (int) $views);
        }

        $chapter = Chapter::where('comic_id', $comic->id)->where('number', $number)->first();

        if ($chapter) {
            $chapter->fill($attrs);
            if ($chapter->isDirty()) {
                $chapter->save();
                return 'updated';
            }
            return 'skipped';
        }

        Chapter::create($attrs + ['comic_id' => $comic->id, 'number' => $number]);

        return 'created';
    }

    private function importChapterImage(array $d, callable $warn): string
    {
        $comic = $this->findComic($this->str($d, 'comic'));
        $number = $this->chapterNumber($d['chapter'] ?? null);

        $chapter = Chapter::where('comic_id', $comic->id)->where('number', $number)->first()
            ?? throw new InvalidArgumentException(
                "Không tìm thấy chương {$number} của truyện '{$comic->title}' (hãy nhập file chương trước)."
            );

        $path = $this->str($d, 'image_path') ?? throw new InvalidArgumentException('Thiếu đường dẫn ảnh.');
        $path = $this->cleanPath($path);

        if (! Storage::disk('public')->exists($path)) {
            $warn("Chưa thấy file ảnh '{$path}' trong storage/app/public.");
        }

        $order = $this->num($d, 'order');
        $image = ChapterImage::where('chapter_id', $chapter->id)->where('image_path', $path)->first();

        if ($image) {
            if ($order !== null && (int) $order !== (int) $image->order) {
                $image->update(['order' => (int) $order]);
                return 'updated';
            }
            return 'skipped';
        }

        if ($order === null) {
            $this->orderCursor[$chapter->id] ??= (int) ChapterImage::where('chapter_id', $chapter->id)->max('order');
            $order = ++$this->orderCursor[$chapter->id];
        }

        ChapterImage::create([
            'chapter_id' => $chapter->id,
            'image_path' => $path,
            'order'      => (int) $order,
        ]);

        return 'created';
    }

    // ------------------------------------------------------------------
    // Hàm tiện ích
    // ------------------------------------------------------------------

    private function str(array $d, string $key): ?string
    {
        $value = $d[$key] ?? null;
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function num(array $d, string $key): ?float
    {
        $value = $d[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("Giá trị '{$value}' ở cột '{$key}' không phải là số.");
        }

        return (float) $value;
    }

    private function chapterNumber(mixed $value): float
    {
        if ($value === null || $value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException('Số chương không hợp lệ (phải là số, ví dụ 1 hoặc 4.5).');
        }

        return round((float) $value, 1);
    }

    private function findComic(?string $name): Comic
    {
        if ($name === null) {
            throw new InvalidArgumentException('Thiếu tên truyện.');
        }

        $key = Str::lower($name);

        if (! isset($this->comicCache[$key])) {
            $comic = Comic::where('title', $name)->orWhere('slug', Str::slug($name))->first();

            if (! $comic) {
                throw new InvalidArgumentException("Không tìm thấy truyện '{$name}' (hãy nhập file truyện trước).");
            }

            $this->comicCache[$key] = $comic;
        }

        return $this->comicCache[$key];
    }

    private function mapStatus(string $value, callable $warn): string
    {
        $key = Str::of($value)->trim()->lower()->ascii()->toString();

        $map = [
            'ongoing' => 'ongoing', 'dang tien hanh' => 'ongoing', 'dang ra' => 'ongoing',
            'completed' => 'completed', 'hoan thanh' => 'completed', 'full' => 'completed',
            'dropped' => 'dropped', 'tam ngung' => 'dropped', 'drop' => 'dropped',
        ];

        if (! isset($map[$key])) {
            $warn("Trạng thái '{$value}' không hợp lệ, dùng 'ongoing' (đang tiến hành).");
            return 'ongoing';
        }

        return $map[$key];
    }

    /** Chuẩn hóa đường dẫn: bỏ dấu \ và tiền tố storage/ hoặc public/ */
    private function cleanPath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', trim($path)), '/');

        return preg_replace('#^(storage|public)/#i', '', $path);
    }

    private function uniqueSlug(string $model, string $text): string
    {
        $base = Str::slug($text) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
