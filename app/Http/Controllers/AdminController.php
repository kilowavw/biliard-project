<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service; // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Tambahkan ini

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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'bos', 'supervisor', 'kasir', 'pemandu'])], // Tambah 'pemandu'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in(['admin', 'bos', 'supervisor', 'kasir', 'pemandu'])], // Tambah 'pemandu'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->role = $request->role;
        $user->save();

        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil diperbarui!');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'Pengguna berhasil dihapus!');
    }


    // --- Metode CRUD untuk Services (BARU) ---
    public function services()
    {
        $services = Service::all();
        $categories = ['alat', 'makanan', 'minuman']; // Definisikan kategori yang tersedia
        return view('admin.services', compact('services', 'categories')); // Menggunakan view baru 'admin.services'
    }

    public function storeService(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:services,nama',
            'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman'])],
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        Service::create($request->all());

        return redirect()->route('admin.services')->with('success', 'Service berhasil ditambahkan!');
    }

    public function updateService(Request $request, Service $service)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
            'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman'])],
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $service->update($request->all());

        return redirect()->route('admin.services')->with('success', 'Service berhasil diperbarui!');
    }

    public function deleteService(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services')->with('success', 'Service berhasil dihapus!');
    }
}