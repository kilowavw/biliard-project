<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:admin,kasir,supervisor,bos',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json(['success' => 'User berhasil ditambahkan.']);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::id() == $user->id) {
            $request->merge(['role' => $user->role]); // mencegah edit role diri sendiri
        }

        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users,email,' . $id,
            'role'     => 'required|in:admin,kasir,supervisor,bos',
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ]);

        return response()->json(['success' => 'User berhasil diperbarui.']);
    }

    public function deleteUser($id)
    {
        if (Auth::id() == $id) {
            return response()->json(['error' => 'Tidak bisa menghapus akun sendiri.'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['success' => 'User berhasil dihapus.']);
    }
}
