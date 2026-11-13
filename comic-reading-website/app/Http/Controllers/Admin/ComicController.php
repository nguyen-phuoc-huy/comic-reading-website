<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comic;
use App\Services\ComicStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ComicController extends Controller
{
    public function index(Request $request)
    {
        $comics = Comic::with('author')
            ->withCount('chapters')
            ->when($request->q, fn ($q, $kw) => $q->where('title', 'like', "%{$kw}%"))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.comics.index', compact('comics'));
    }

    public function create()
    {
        return view('admin.comics.create', [
            'authors'    => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ComicStorage $storage)
    {
        $data = $this->validated($request);
        unset($data['cover'], $data['categories']);

        $data['slug'] = $this->uniqueSlug($data['title']);

        $comic = Comic::create($data);

        // Ảnh bìa: comics/{slug}/{slug}-cover.jpg
        if ($request->hasFile('cover')) {
            $comic->update(['cover' => $storage->storeCover($comic, $request->file('cover'))]);
        }

        $comic->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.comics.index')
            ->with('success', 'Đã thêm truyện.');
    }

    public function edit(Comic $comic)
    {
        return view('admin.comics.edit', [
            'comic'      => $comic->load('categories'),
            'authors'    => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Comic $comic, ComicStorage $storage)
    {
        $data = $this->validated($request);
        unset($data['cover'], $data['categories']);

        // Mặc định giữ nguyên slug (không đổi liên kết cũ). Chỉ đổi khi tích chọn và tên truyện thực sự đổi.
        $renameSlug = $request->boolean('rename_slug') && $data['title'] !== $comic->title;
        if ($renameSlug) {
            $data['slug'] = $this->uniqueSlug($data['title'], $comic->id);
        }

        $newCover = $request->file('cover');
        if ($newCover) {
            $storage->deleteCover($comic);
        }

        $comic->update($data);

        if ($newCover) {
            $comic->update(['cover' => $storage->storeCover($comic, $newCover)]);
        }

        $comic->categories()->sync($request->input('categories', []));

        // Đổi slug -> chuyển thư mục và đổi tên toàn bộ ảnh theo tên mới
        if ($renameSlug) {
            $storage->organizeComic($comic->refresh());
        }

        return redirect()->route('admin.comics.index')
            ->with('success', $renameSlug
                ? 'Đã cập nhật truyện, đổi đường dẫn và thư mục ảnh theo tên mới.'
                : 'Đã cập nhật truyện.');
    }

    // POST admin/comics/{comic}/organize
    public function organize(Comic $comic, ComicStorage $storage)
    {
        @set_time_limit(300);

        $report = $storage->organizeComic($comic);

        return back()->with('success', "Đã chuẩn hóa {$report['images']} ảnh trong {$report['chapters']} chương vào thư mục comics/{$comic->slug}.");
    }

    public function destroy(Comic $comic)
    {
        // Xóa mềm (SoftDeletes), có thể khôi phục sau này, giữ nguyên ảnh
        $comic->delete();

        return redirect()->route('admin.comics.index')
            ->with('success', 'Đã xóa truyện.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'author_id'    => ['nullable', 'exists:authors,id'],
            'other_names'  => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'status'       => ['required', 'in:ongoing,completed,dropped'],
            'cover'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'categories'   => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Comic::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
