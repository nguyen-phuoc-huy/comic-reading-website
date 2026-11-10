@php $editing = (bool) $user; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Tên hiển thị <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $user?->name) }}" required maxlength="255"
               class="form-control @error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" value="{{ old('email', $user?->email) }}" required maxlength="255"
               class="form-control @error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Vai trò <span class="text-danger">*</span></label>
        <select name="role" class="form-select @error('role') is-invalid @enderror" required>
            <option value="user" @selected(old('role', $user?->role ?? 'user') === 'user')>Người dùng</option>
            <option value="admin" @selected(old('role', $user?->role) === 'admin')>Quản trị viên</option>
        </select>
        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">Quản trị viên được vào trang quản trị và quản lý toàn bộ dữ liệu.</div>
    </div>

    <div class="col-md-6"></div>

    <div class="col-md-6">
        <label class="form-label">
            Mật khẩu @if (! $editing)<span class="text-danger">*</span>@endif
        </label>
        <input type="password" name="password" autocomplete="new-password" minlength="8"
               @required(! $editing)
               class="form-control @error('password') is-invalid @enderror">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">
            {{ $editing ? 'Để trống nếu không muốn đổi mật khẩu. ' : '' }}Tối thiểu 8 ký tự.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nhập lại mật khẩu</label>
        <input type="password" name="password_confirmation" autocomplete="new-password" class="form-control">
    </div>
</div>
