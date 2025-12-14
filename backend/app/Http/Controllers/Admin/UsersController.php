<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->latest()->paginate(10);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'roles' => 'array'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);
        
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'roles' => 'array'
        ]);

        if ($request->has('password') && $request->password) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself'], 403);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
