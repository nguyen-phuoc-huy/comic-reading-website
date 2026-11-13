<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    // GET /auth/google  -> chuyển sang trang đăng nhập của Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // GET /auth/google/callback  -> Google trả người dùng về đây
    public function callback()
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            report($e);

            // Khi APP_DEBUG=true thì hiện luôn nguyên nhân thật để dễ tìm lỗi lúc cài đặt
            $detail = config('app.debug') ? ' (' . Str::limit($e->getMessage(), 250) . ')' : '';

            return redirect()->route('login')
                ->withErrors(['email' => 'Đăng nhập bằng Google không thành công, vui lòng thử lại.' . $detail]);
        }

        $email = $google->getEmail();
        if (! $email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Không lấy được email từ tài khoản Google.']);
        }

        // 1) Đã từng đăng nhập bằng Google này
        $user = User::where('google_id', $google->getId())->first();

        // 2) Chưa có, nhưng email đã tồn tại -> liên kết (chỉ khi Google xác nhận email)
        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $verified = (bool) ($google->user['email_verified'] ?? $google->user['verified_email'] ?? false);

                if (! $verified) {
                    return redirect()->route('login')
                        ->withErrors(['email' => 'Email Google chưa được xác minh nên không thể liên kết với tài khoản hiện có.']);
                }

                $user->forceFill(['google_id' => $google->getId()]);
            }
        }

        // 3) Người dùng mới hoàn toàn -> tạo tài khoản
        if (! $user) {
            $user = (new User)->forceFill([
                'name'     => $google->getName() ?: Str::before($email, '@'),
                'email'    => $email,
                'google_id' => $google->getId(),
                'role'     => 'user',
                'password' => null,
            ]);
        }

        // Cập nhật ảnh đại diện, đánh dấu email đã xác minh
        $user->forceFill([
            'avatar'            => $google->getAvatar() ?: $user->avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        Auth::login($user, true);

        // Quản trị viên vào thẳng trang quản trị, người dùng thường về trang chủ
        return redirect()->intended($user->role === 'admin' ? route('admin.dashboard') : route('home'));
    }
}