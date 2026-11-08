<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthorController extends Controller
{
    public function index(Request $request)
    {
        $authors = Author::withCount('comics')
            ->when($request->q, fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.authors.index', compact('authors'));
    }

    public function create()
    {
        return view('admin.authors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:authors,name'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['name']);

        Author::create($data);

        return redirect()->route('admin.authors.index')
            ->with('success', 'Đã thêm tác giả.');
    }

    public function edit(Author $author)
    {
        return view('admin.authors.edit', compact('author'));
    }

    public function update(Request $request, Author $author)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('authors', 'name')->ignore($author->id)],
        ]);

        // Giữ nguyên slug cũ để không làm hỏng đường dẫn đã có
        $author->update($data);

        return redirect()->route('admin.authors.index')
            ->with('success', 'Đã cập nhật tác giả.');
    }

    public function destroy(Author $author)
    {
        // Truyện của tác giả này sẽ có author_id = null (nullOnDelete)
        $author->delete();

        return redirect()->route('admin.authors.index')
            ->with('success', 'Đã xóa tác giả.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (Author::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}