<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // GET admin/users
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->q, function ($query, $keyword) {
                $query->where(function ($w) use ($keyword) {
                    $w->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when(in_array($request->role, ['user', 'admin'], true), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // GET admin/users/create
    public function create()
    {
        return view('admin.users.create');
    }

    // POST admin/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // forceFill để không phụ thuộc vào $fillable của model
        $user = (new User)->forceFill([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'role'              => $data['role'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(), // tài khoản do admin tạo coi như đã xác minh
        ]);
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã thêm người dùng.');
    }

    // GET admin/users/{user}/edit
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // PUT/PATCH admin/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Không cho hạ quyền chính mình hoặc quản trị viên cuối cùng
        if ($user->role === 'admin' && $data['role'] !== 'admin') {
            if ($user->id === $request->user()->id) {
                return back()->withInput()->with('error', 'Bạn không thể tự hạ quyền quản trị của chính mình.');
            }
            if ($this->isLastAdmin($user)) {
                return back()->withInput()->with('error', 'Không thể hạ quyền quản trị viên cuối cùng.');
            }
        }

        $user->forceFill([
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $user->forceFill(['password' => Hash::make($data['password'])]);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã cập nhật người dùng.');
    }

    // DELETE admin/users/{user}
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình.');
        }

        if ($user->role === 'admin' && $this->isLastAdmin($user)) {
            return back()->with('error', 'Không thể xóa quản trị viên cuối cùng.');
        }

        // Bình luận, lịch sử đọc, danh sách theo dõi của người này tự xóa theo (cascadeOnDelete)
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Đã xóa người dùng.');
    }

    private function isLastAdmin(User $user): bool
    {
        return User::where('role', 'admin')->where('id', '!=', $user->id)->doesntExist();
    }
}
