@php
    $map = [
        'ongoing'   => ['Đang tiến hành', 'primary'],
        'completed' => ['Hoàn thành', 'success'],
        'dropped'   => ['Tạm ngưng', 'secondary'],
    ];
    [$label, $color] = $map[$status] ?? [$status, 'light'];
@endphp
<span class="badge text-bg-{{ $color }}">{{ $label }}</span>
