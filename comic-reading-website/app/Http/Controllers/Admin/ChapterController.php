<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterImage;
use App\Models\Comic;
use App\Services\ComicStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ChapterController extends Controller
{
    // GET admin/comics/{comic}/chapters
    public function index(Comic $comic)
    {
        $chapters = $comic->chapters()
            ->withCount('images')
            ->paginate(30);

        return view('admin.chapters.index', compact('comic', 'chapters'));
    }

    // GET admin/comics/{comic}/chapters/create
    public function create(Comic $comic)
    {
        // Gợi ý số chương tiếp theo
        $nextNumber = (float) $comic->chapters()->max('number') + 1;

        return view('admin.chapters.create', compact('comic', 'nextNumber'));
    }

    // POST admin/comics/{comic}/chapters
    public function store(Request $request, Comic $comic, ComicStorage $storage)
    {
        $data = $request->validate([
            'number'   => [
                'required',
                'numeric',
                'min:0',
                Rule::unique('chapters', 'number')->where('comic_id', $comic->id),
            ],
            'title'    => ['nullable', 'string', 'max:255'],
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($comic, $data, $request, $storage) {
            $chapter = $comic->chapters()->create([
                'number' => $data['number'],
                'title'  => $data['title'] ?? null,
            ]);
            $chapter->setRelation('comic', $comic);

            $this->saveImages($chapter, $request->file('images'), $storage);
        });

        return redirect()->route('admin.comics.chapters.index', $comic)
            ->with('success', 'Đã thêm chương.');
    }

    // GET admin/chapters/{chapter}/edit
    public function edit(Chapter $chapter, ComicStorage $storage)
    {
        $chapter->load('comic', 'images');
        $folder = $storage->chapterDir($chapter->comic, $chapter);

        return view('admin.chapters.edit', compact('chapter', 'folder'));
    }

    // PUT/PATCH admin/chapters/{chapter}
    public function update(Request $request, Chapter $chapter, ComicStorage $storage)
    {
        $data = $request->validate([
            'number'   => [
                'required',
                'numeric',
                'min:0',
                Rule::unique('chapters', 'number')
                    ->where('comic_id', $chapter->comic_id)
                    ->ignore($chapter->id),
            ],
            'title'    => ['nullable', 'string', 'max:255'],
            'images'   => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $numberChanged = round((float) $chapter->number, 1) !== round((float) $data['number'], 1);

        DB::transaction(function () use ($chapter, $data, $request, $storage) {
            $chapter->update([
                'number' => $data['number'],
                'title'  => $data['title'] ?? null,
            ]);

            if ($request->hasFile('images')) {
                $this->saveImages($chapter->load('comic'), $request->file('images'), $storage);
            }
        });

        // Số chương nằm trong tên thư mục và tên file nên đổi số chương thì đổi tên theo
        if ($numberChanged) {
            $storage->renumberChapter($chapter->refresh()->load('comic'));
        }

        return redirect()->route('admin.comics.chapters.index', $chapter->comic_id)
            ->with('success', 'Đã cập nhật chương.');
    }

    // DELETE admin/chapters/{chapter}
    public function destroy(Chapter $chapter, ComicStorage $storage)
    {
        $comicId = $chapter->comic_id;

        $storage->deleteChapterFiles($chapter);
        $chapter->delete(); // chapter_images tự xóa nhờ cascadeOnDelete

        return redirect()->route('admin.comics.chapters.index', $comicId)
            ->with('success', 'Đã xóa chương.');
    }

    // DELETE admin/chapters/{chapter}/images/{image}
    public function destroyImage(Chapter $chapter, ChapterImage $image, ComicStorage $storage)
    {
        abort_unless($image->chapter_id === $chapter->id, 404);

        if ($storage->disk()->exists($image->image_path)) {
            $storage->disk()->delete($image->image_path);
        }
        $storage->deleteDirIfEmpty(dirname($image->image_path));
        $image->delete();

        return back()->with('success', 'Đã xóa ảnh.');
    }

    /**
     * Lưu danh sách ảnh tải lên: sắp theo tên file (kiểu tự nhiên: 2.jpg < 10.jpg),
     * đặt tên dạng ten-truyen-chap-1-001.jpg trong thư mục comics/ten-truyen/chap-1/.
     */
    private function saveImages(Chapter $chapter, array $files, ComicStorage $storage): void
    {
        usort($files, fn($a, $b) => strnatcasecmp(
            $a->getClientOriginalName(),
            $b->getClientOriginalName()
        ));

        $order = (int) ChapterImage::where('chapter_id', $chapter->id)->max('order');

        foreach ($files as $file) {
            $order++;
            $path = $storage->storeUploaded($chapter, $file, $order);

            $chapter->images()->create([
                'image_path' => $path,
                'order'      => $order,
            ]);
        }
    }
}
