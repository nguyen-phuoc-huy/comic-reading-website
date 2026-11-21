<?php

namespace App\Http\Controllers;

use App\Support\Avatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    // PUT /ho-so/avatar
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=64,min_height=64'],
        ], [
            'avatar.required'   => 'Vui lòng chọn một ảnh.',
            'avatar.image'      => 'File phải là ảnh.',
            'avatar.mimes'      => 'Ảnh phải có định dạng JPG, PNG hoặc WebP.',
            'avatar.max'        => 'Ảnh quá lớn (tối đa 2MB).',
            'avatar.dimensions' => 'Ảnh quá nhỏ (tối thiểu 64x64 pixel).',
            'avatar.uploaded'   => 'Tải ảnh lên không thành công. Hãy thử ảnh nhỏ hơn 2MB.',
        ]);

        $user = $request->user();
        $file = $request->file('avatar');
        $disk = Storage::disk('public');
        $disk->makeDirectory('avatars');

        // Tên có mốc thời gian: đổi ảnh là đổi địa chỉ nên trình duyệt không giữ ảnh cũ
        $name = sprintf('user-%d-%d', $user->id, time());
        $path = null;

        // Cắt vuông ở giữa, thu nhỏ 256x256 cho nhẹ (cần thư viện GD)
        if (function_exists('imagecreatetruecolor')) {
            $relative = "avatars/{$name}.jpg";
            if ($this->makeSquareJpeg($file->getRealPath(), $disk->path($relative), 256)) {
                $path = $relative;
            }
        }

        // Không xử lý được thì lưu nguyên ảnh gốc
        if (! $path) {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $path = $file->storeAs('avatars', "{$name}.{$ext}", 'public');
        }

        Avatar::deleteFile($user->avatar); // xóa ảnh cũ do người dùng tải lên

        $user->forceFill(['avatar' => $path])->save();

        return redirect()->route('profile.edit')->with('success', 'Đã cập nhật ảnh đại diện.');
    }

    // DELETE /ho-so/avatar
    public function destroy(Request $request)
    {
        $user = $request->user();

        Avatar::deleteFile($user->avatar);
        $user->forceFill(['avatar' => null])->save();

        return redirect()->route('profile.edit')->with('success', 'Đã xóa ảnh đại diện.');
    }

    /**
     * Cắt ảnh thành hình vuông (lấy phần giữa), thu về $size x $size, lưu JPEG.
     */
    private function makeSquareJpeg(string $source, string $destination, int $size): bool
    {
        @ini_set('memory_limit', '256M');

        $info = @getimagesize($source);
        if (! $info) {
            return false;
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : false,
            IMAGETYPE_PNG  => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            default        => false,
        };

        if (! $image) {
            return false;
        }

        // Ảnh chụp từ điện thoại thường lưu kèm hướng xoay (EXIF): xoay lại cho đúng chiều
        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($source);
            $angle = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };
            if ($angle !== 0 && ($rotated = imagerotate($image, $angle, 0))) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $side   = min($width, $height);
        $x = (int) (($width - $side) / 2);
        $y = (int) (($height - $side) / 2);

        $canvas = imagecreatetruecolor($size, $size);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255)); // nền trắng cho ảnh PNG trong suốt
        imagecopyresampled($canvas, $image, 0, 0, $x, $y, $size, $size, $side, $side);

        $ok = imagejpeg($canvas, $destination, 88);

        imagedestroy($image);
        imagedestroy($canvas);

        return $ok;
    }
}
