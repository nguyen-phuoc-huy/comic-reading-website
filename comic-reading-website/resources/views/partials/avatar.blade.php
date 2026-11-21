{{-- Ảnh đại diện tròn; chưa có ảnh thì hiện chữ cái đầu tên.
     Tham số: $user, $size (px, mặc định 40), $class (lớp CSS thêm) --}}
@php
    $avatarUrl = \App\Support\Avatar::url($user);
    $size = $size ?? 40;
@endphp

@if ($avatarUrl)
    <img src="{{ $avatarUrl }}" alt="" width="{{ $size }}" height="{{ $size }}" loading="lazy" referrerpolicy="no-referrer"
         class="rounded-circle flex-shrink-0 {{ $class ?? '' }}" style="object-fit:cover">
@else
    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0 {{ $class ?? '' }}"
         style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ round($size * 0.42) }}px">
        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
@endif
