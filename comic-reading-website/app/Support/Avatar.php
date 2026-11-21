<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Ảnh đại diện có thể là:
 *  - đường dẫn tương đối trong storage/app/public (ảnh người dùng tải lên), ví dụ "avatars/user-5-1731500000.jpg"
 *  - địa chỉ http(s) đầy đủ (ảnh lấy từ tài khoản Google)
 */
class Avatar
{
    /** Địa chỉ ảnh để hiển thị, hoặc null nếu chưa có ảnh */
    public static function url($user): ?string
    {
        $avatar = is_object($user) ? $user->avatar : $user;

        if (! $avatar) {
            return null;
        }

        return self::isExternal($avatar) ? $avatar : asset('storage/' . $avatar);
    }

    public static function isExternal(?string $avatar): bool
    {
        return (bool) $avatar && Str::startsWith($avatar, ['http://', 'https://']);
    }

    /** Có phải ảnh do người dùng tải lên (lưu trong storage)? */
    public static function isUploaded(?string $avatar): bool
    {
        return (bool) $avatar && ! self::isExternal($avatar);
    }

    /** Xóa file ảnh đã tải lên (không làm gì với ảnh Google) */
    public static function deleteFile(?string $avatar): void
    {
        if (self::isUploaded($avatar) && Storage::disk('public')->exists($avatar)) {
            Storage::disk('public')->delete($avatar);
        }
    }
}
