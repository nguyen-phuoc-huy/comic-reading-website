<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['title']);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $comic = Comic::create($data);
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

    public function update(Request $request, Comic $comic)
    {
        $data = $this->validated($request);

        // Giữ nguyên slug cũ để không làm hỏng đường dẫn đã có

        if ($request->hasFile('cover')) {
            if ($comic->cover) {
                Storage::disk('public')->delete($comic->cover);
            }
            $data['cover'] = $request->file('cover')->store('covers', 'public');
        }

        $comic->update($data);
        $comic->categories()->sync($request->input('categories', []));

        return redirect()->route('admin.comics.index')
            ->with('success', 'Đã cập nhật truyện.');
    }

    public function destroy(Comic $comic)
    {
        // Xóa mềm (SoftDeletes), có thể khôi phục sau này
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

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Comic::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}