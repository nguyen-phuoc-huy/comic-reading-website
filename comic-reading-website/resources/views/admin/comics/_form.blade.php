@php
$selected = old('categories', $comic ? $comic->categories->pluck('id')->all() : []);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Tên truyện <span class="text-danger">*</span></label>
        <input type="text" name="title" value="{{ old('title', $comic?->title) }}" required maxlength="255"
            class="form-control @error('title') is-invalid @enderror">
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach (['ongoing' => 'Đang tiến hành', 'completed' => 'Hoàn thành', 'dropped' => 'Tạm ngưng'] as $value
            => $label)
            <option value="{{ $value }}" @selected(old('status', $comic?->status ?? 'ongoing') === $value)>{{ $label }}
            </option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên khác</label>
        <input type="text" name="other_names" value="{{ old('other_names', $comic?->other_names) }}" maxlength="255"
            class="form-control @error('other_names') is-invalid @enderror">
        @error('other_names')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Tác giả</label>
        <select name="author_id" class="form-select @error('author_id') is-invalid @enderror">
            <option value="">-- Chưa rõ --</option>
            @foreach ($authors as $author)
            <option value="{{ $author->id }}" @selected((string) old('author_id', $comic?->author_id) === (string)
                $author->id)>
                {{ $author->name }}
            </option>
            @endforeach
        </select>
        @error('author_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label">Nội dung / mô tả</label>
        <textarea name="description" rows="5"
            class="form-control @error('description') is-invalid @enderror">{{ old('description', $comic?->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if ($comic)
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="rename_slug" value="1" id="rename_slug">
            <label class="form-check-label" for="rename_slug">
                Nếu đổi tên truyện: đổi luôn đường dẫn (slug) và thư mục ảnh theo tên mới
            </label>
            <div class="form-text">
                Hiện tại: <code>{{ $comic->slug }}</code>. Tích chọn thì thư mục <code>comics/{{ $comic->slug }}</code>
                và tên các file ảnh
                sẽ được đổi theo tên mới, nhưng liên kết cũ của truyện này sẽ không còn dùng được.
            </div>
        </div>
    </div>
    @endif

    <div class="col-md-6">
        <label class="form-label">Ảnh bìa (jpg, png, webp, tối đa 3MB)</label>
        <input type="file" name="cover" accept="image/*" class="form-control @error('cover') is-invalid @enderror">
        @error('cover')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if ($comic?->cover)
        <img src="{{ asset('storage/' . $comic->cover) }}" class="rounded mt-2" style="height:120px" alt="Bìa hiện tại">
        <div class="form-text">Chọn ảnh mới nếu muốn thay.</div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">Thể loại</label>
        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-6 g-1">
            @foreach ($categories as $category)
            <div class="col">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" id="cat-{{ $category->id }}"
                        value="{{ $category->id }}" @checked(in_array($category->id, $selected))>
                    <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}</label>
                </div>
            </div>
            @endforeach
        </div>
        @error('categories.*')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>