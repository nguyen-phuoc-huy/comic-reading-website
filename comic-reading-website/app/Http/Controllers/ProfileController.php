<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\ReadingHistory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // GET /ho-so
    public function edit(Request $request)
    {
        $user = $request->user();

        $stats = [
            'follows'   => $user->followedComics()->count(),
            'histories' => ReadingHistory::where('user_id', $user->id)->count(),
            'comments'  => Comment::where('user_id', $user->id)->count(),
        ];

        return view('profile.edit', compact('user', 'stats'));
    }

    // PUT /ho-so  (cập nhật tên, email)
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'name.required'  => 'Vui lòng nhập tên hiển thị.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email'    => 'Email không hợp lệ.',
            'email.unique'   => 'Email này đã có người sử dụng.',
        ]);

        $user->forceFill([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        // Nếu hệ thống bật xác minh email thì đổi email phải xác minh lại
        if ($user->isDirty('email') && $user instanceof MustVerifyEmail) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    // PUT /ho-so/mat-khau  (đổi mật khẩu)
    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required'         => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.required'                 => 'Vui lòng nhập mật khẩu mới.',
            'password.min'                      => 'Mật khẩu mới tối thiểu 8 ký tự.',
            'password.confirmed'                => 'Mật khẩu nhập lại không khớp.',
            'password.different'                => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()->route('profile.edit')->with('success', 'Đã đổi mật khẩu.');
    }
}
