<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('roles')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);
        activity()->performedOn($user)->log('created');

        return redirect()->route('users.index')->with('success', 'Đã thêm người dùng.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', "unique:users,email,{$user->id}"],
            'role'  => ['required', 'exists:roles,name'],
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);
        $user->syncRoles([$data['role']]);

        activity()->performedOn($user)->log('updated');

        return redirect()->route('users.index')->with('success', 'Đã cập nhật người dùng.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        $user->delete();
        activity()->performedOn($user)->log('deleted');

        return redirect()->route('users.index')->with('success', 'Đã xóa người dùng.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = Str::random(10);
        $user->update(['password' => Hash::make($newPassword)]);

        activity()->performedOn($user)->log('reset-password');

        return back()->with('success', "Mật khẩu mới: {$newPassword}");
    }
}
