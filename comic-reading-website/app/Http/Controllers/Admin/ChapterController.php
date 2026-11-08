<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterImage;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
    public function store(Request $request, Comic $comic)
    {
        $data = $request->validate([
            'number'   => [
                'required', 'numeric', 'min:0',
                Rule::unique('chapters', 'number')->where('comic_id', $comic->id),
            ],
            'title'    => ['nullable', 'string', 'max:255'],
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($comic, $data, $request) {
            $chapter = $comic->chapters()->create([
                'number' => $data['number'],
                'title'  => $data['title'] ?? null,
            ]);

            $this->saveImages($chapter, $request->file('images'));
        });

        return redirect()->route('admin.comics.chapters.index', $comic)
            ->with('success', 'Đã thêm chương.');
    }

    // GET admin/chapters/{chapter}/edit
    public function edit(Chapter $chapter)
    {
        $chapter->load('comic', 'images');

        return view('admin.chapters.edit', compact('chapter'));
    }

    // PUT/PATCH admin/chapters/{chapter}
    public function update(Request $request, Chapter $chapter)
    {
        $data = $request->validate([
            'number'   => [
                'required', 'numeric', 'min:0',
                Rule::unique('chapters', 'number')
                    ->where('comic_id', $chapter->comic_id)
                    ->ignore($chapter->id),
            ],
            'title'    => ['nullable', 'string', 'max:255'],
            'images'   => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        DB::transaction(function () use ($chapter, $data, $request) {
            $chapter->update([
                'number' => $data['number'],
                'title'  => $data['title'] ?? null,
            ]);

            // Nếu có chọn thêm ảnh thì nối tiếp vào cuối chương
            if ($request->hasFile('images')) {
                $this->saveImages($chapter, $request->file('images'));
            }
        });

        return redirect()->route('admin.comics.chapters.index', $chapter->comic_id)
            ->with('success', 'Đã cập nhật chương.');
    }

    // DELETE admin/chapters/{chapter}
    public function destroy(Chapter $chapter)
    {
        $comicId = $chapter->comic_id;

        Storage::disk('public')->deleteDirectory("comics/{$comicId}/{$chapter->id}");
        $chapter->delete(); // chapter_images tự xóa nhờ cascadeOnDelete

        return redirect()->route('admin.comics.chapters.index', $comicId)
            ->with('success', 'Đã xóa chương.');
    }

    // DELETE admin/chapters/{chapter}/images/{image}
    public function destroyImage(Chapter $chapter, ChapterImage $image)
    {
        abort_unless($image->chapter_id === $chapter->id, 404);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('success', 'Đã xóa ảnh.');
    }

    /**
     * Lưu danh sách ảnh, sắp xếp theo tên file (kiểu tự nhiên: 2.jpg < 10.jpg)
     * rồi đánh số thứ tự nối tiếp các ảnh đã có.
     */
    private function saveImages(Chapter $chapter, array $files): void
    {
        usort($files, fn ($a, $b) => strnatcasecmp(
            $a->getClientOriginalName(),
            $b->getClientOriginalName()
        ));

        $order = (int) ChapterImage::where('chapter_id', $chapter->id)->max('order');

        foreach ($files as $file) {
            $path = $file->store("comics/{$chapter->comic_id}/{$chapter->id}", 'public');

            $chapter->images()->create([
                'image_path' => $path,
                'order'      => ++$order,
            ]);
        }
    }
}