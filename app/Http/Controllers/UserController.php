<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\MinioService;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function __construct(protected MinioService $minio) {}

    public function index(Request $request)
    {
        $isSuperAdmin = auth()->user()?->roles()->where('slug', 'superadmin')->exists();
        $tenantId = $isSuperAdmin ? null : current_tenant_id();

        $users = User::getPaginatedUsers($request->query('search'), 10, $tenantId);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $isSuperAdmin = auth()->user()?->roles()->where('slug', 'superadmin')->exists();
        
        // Tenant Owner hanya boleh membuat akun Tenant Staff (atau role staff/kasir)
        $roles = $isSuperAdmin 
            ? Role::getAllOrdered() 
            : Role::whereIn('slug', ['staff'])->get();

        $tenants = $isSuperAdmin ? \App\Models\Tenant::all() : collect();

        return view('admin.users.create', compact('roles', 'tenants', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $tenantId = current_tenant_id();

        if (!$tenantId && $request->filled('tenant_id')) {
            $tenantId = (int) $request->input('tenant_id');
        }

        $request->validate([
            'tenant_id' => 'nullable|exists:tenants,id',
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role_id'   => 'nullable|exists:roles,id',
            'phone'     => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:500',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = minio_upload($request->file('avatar'), 'avatars');
        }

        $createdUser = User::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role_id'   => $request->role_id,
            'phone'     => $request->phone,
            'address'   => $request->address,
            'avatar'    => $avatarPath,
        ]);

        // Sync role pivot table
        if ($request->filled('role_id')) {
            $createdUser->roles()->sync([$request->role_id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function show(User $user)
    {
        $user->load('role', 'roles');

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $isSuperAdmin = auth()->user()?->roles()->where('slug', 'superadmin')->exists();

        // Tenant Owner hanya boleh melihat dan mengedit role Staff
        $roles = $isSuperAdmin 
            ? Role::getAllOrdered() 
            : Role::whereIn('slug', ['staff'])->get();

        $user->load('role', 'roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'role_id'  => 'nullable|exists:roles,id',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string|max:500',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name'    => $request->name,
            'email'   => $request->email,
            'role_id' => $request->role_id,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Upload avatar baru & hapus yang lama
        if ($request->hasFile('avatar')) {
            $data['avatar'] = minio_replace($user->avatar, $request->file('avatar'), 'avatars');
        }

        // Hapus avatar jika checkbox remove dicentang
        if ($request->has('remove_avatar') && $user->avatar) {
            minio_delete($user->avatar);
            $data['avatar'] = null;
        }

        $user->update($data);

        // Sync role pivot table
        if ($request->filled('role_id')) {
            $user->roles()->sync([$request->role_id]);
        } else {
            $user->roles()->detach();
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    public function destroy(User $user)
    {
        if ($user->id === session('user_id')) {
            return redirect()->route('users.index')
                ->with('error', 'Tidak bisa menghapus akun yang sedang login!');
        }

        // Hapus avatar dari storage
        if ($user->avatar) {
            minio_delete($user->avatar);
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}
