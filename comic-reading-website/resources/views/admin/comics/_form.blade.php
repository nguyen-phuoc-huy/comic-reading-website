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

    {{-- Thể loại: có ô tìm kiếm, danh sách cuộn được và các thể loại đã chọn hiện thành nhãn --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0" for="catSearch">Thể loại</label>
            <span class="small text-muted">Đã chọn: <strong id="catCount">0</strong> / {{ $categories->count() }}</span>
        </div>

        {{-- Các thể loại đã chọn --}}
        <div id="selectedCats" class="d-flex flex-wrap gap-1 mb-2" style="min-height:1.6rem"></div>

        {{-- Ô tìm kiếm + nút tiện ích --}}
        <div class="input-group input-group-sm mb-2">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="search" id="catSearch" class="form-control"
                placeholder="Gõ để tìm thể loại (không cần gõ dấu)..." autocomplete="off">
            <button type="button" class="btn btn-outline-secondary" id="catClear" title="Xóa nội dung tìm kiếm"><i
                    class="bi bi-x-lg"></i></button>
            <button type="button" class="btn btn-outline-secondary" id="catOnlySelected"
                title="Chỉ hiện các thể loại đã chọn">Chỉ hiện đã chọn</button>
            <button type="button" class="btn btn-outline-danger" id="catUncheckAll" title="Bỏ chọn tất cả thể loại">Bỏ
                chọn hết</button>
        </div>

        {{-- Danh sách (cuộn khi nhiều) --}}
        <div id="catBox" class="border rounded p-2" style="max-height:260px;overflow-y:auto">
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-1">
                @foreach ($categories as $category)
                <div class="col cat-item"
                    data-name="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($category->name)) }}"
                    data-label="{{ $category->name }}">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="categories[]" id="cat-{{ $category->id }}"
                            value="{{ $category->id }}" @checked(in_array($category->id, $selected))>
                        <label class="form-check-label" for="cat-{{ $category->id }}">{{ $category->name }}</label>
                    </div>
                </div>
                @endforeach
            </div>
            <div id="catEmpty" class="text-muted small text-center py-3 d-none">Không tìm thấy thể loại nào phù hợp.
            </div>
        </div>

        @error('categories.*')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>

@push('scripts')
<script>
    (function () {
        var box = document.getElementById('catBox');
        if (!box) return;

        var search = document.getElementById('catSearch');
        var items = Array.prototype.slice.call(box.querySelectorAll('.cat-item'));
        var chips = document.getElementById('selectedCats');
        var counter = document.getElementById('catCount');
        var empty = document.getElementById('catEmpty');
        var onlySelectedBtn = document.getElementById('catOnlySelected');
        var onlySelected = false;

        // Bỏ dấu tiếng Việt, viết thường để tìm "hanh dong" ra "Hành động"
        function norm(text) {
            return text.toLowerCase().normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '').replace(/đ/g, 'd').trim();
        }

        function applyFilter() {
            var q = norm(search.value);
            var shown = 0;

            items.forEach(function (item) {
                var checked = item.querySelector('input').checked;
                var ok = (!q || item.dataset.name.indexOf(q) !== -1) && (!onlySelected || checked);
                item.classList.toggle('d-none', !ok);
                if (ok) shown++;
            });

            empty.classList.toggle('d-none', shown !== 0);
        }

        function renderChips() {
            chips.innerHTML = '';
            var count = 0;

            items.forEach(function (item) {
                var checkbox = item.querySelector('input');
                if (!checkbox.checked) return;
                count++;

                var chip = document.createElement('span');
                chip.className = 'badge text-bg-primary d-inline-flex align-items-center gap-1';
                chip.textContent = item.dataset.label;

                var remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'btn-close btn-close-white';
                remove.style.fontSize = '.55rem';
                remove.setAttribute('aria-label', 'Bỏ chọn ' + item.dataset.label);
                remove.addEventListener('click', function () {
                    checkbox.checked = false;
                    refresh();
                });

                chip.appendChild(remove);
                chips.appendChild(chip);
            });

            counter.textContent = count;
            if (count === 0) {
                chips.innerHTML = '<span class="text-muted small">Chưa chọn thể loại nào.</span>';
            }
        }

        function refresh() { renderChips(); applyFilter(); }

        search.addEventListener('input', applyFilter);
        search.addEventListener('keydown', function (e) { if (e.key === 'Enter') e.preventDefault(); }); // Enter không gửi form
        box.addEventListener('change', refresh);

        document.getElementById('catClear').addEventListener('click', function () {
            search.value = '';
            applyFilter();
            search.focus();
        });

        onlySelectedBtn.addEventListener('click', function () {
            onlySelected = !onlySelected;
            onlySelectedBtn.classList.toggle('active', onlySelected);
            applyFilter();
        });

        document.getElementById('catUncheckAll').addEventListener('click', function () {
            items.forEach(function (item) { item.querySelector('input').checked = false; });
            refresh();
        });

        refresh();
    })();
</script>
@endpush