<?php

// File: app/Http/Controllers/MejaController.php

namespace App\Http\Controllers;

use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MejaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mejas = Meja::orderBy('nama_meja')->paginate(10);
        return view('admin.mejas.index', compact('mejas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validasi langsung di dalam controller
            $request->validate([
                'nama_meja' => 'required|string|max:255|unique:mejas,nama_meja',
            ], [
                'nama_meja.required' => 'Nama meja wajib diisi.',
                'nama_meja.string' => 'Nama meja harus berupa teks.',
                'nama_meja.max' => 'Nama meja tidak boleh lebih dari :max karakter.',
                'nama_meja.unique' => 'Nama meja ini sudah ada, silakan gunakan nama lain.',
            ]);

            Meja::create([
                'nama_meja' => $request->nama_meja,
                'status' => 'kosong', // Status default saat meja baru dibuat
            ]);
            return redirect()->route('admin.mejas.index')->with('success', 'Meja berhasil ditambahkan!');
        } catch (ValidationException $e) {
            // Jika ada error validasi, redirect back dengan error dan old input untuk modal create
            return back()->withErrors($e->errors(), 'storeMeja')->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing meja: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan meja. Silakan coba lagi.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meja $meja) // Menggunakan Route Model Binding
    {
        try {
            // Validasi langsung di dalam controller
            $request->validate([
                'nama_meja' => 'required|string|max:255|unique:mejas,nama_meja,' . $meja->id,
            ], [
                'nama_meja.required' => 'Nama meja wajib diisi.',
                'nama_meja.string' => 'Nama meja harus berupa teks.',
                'nama_meja.max' => 'Nama meja tidak boleh lebih dari :max karakter.',
                'nama_meja.unique' => 'Nama meja ini sudah ada, silakan gunakan nama lain.',
            ]);

            $meja->update([
                'nama_meja' => $request->nama_meja,
                // Status tidak diubah dari form admin biasa, hanya nama meja
                // Status meja diubah saat penyewaan berlangsung/selesai
            ]);
            return redirect()->route('admin.mejas.index')->with('success', 'Meja berhasil diperbarui!');
        } catch (ValidationException $e) {
            // Jika ada error validasi, redirect back dengan error dan old input untuk modal edit
            return back()
                ->withErrors($e->errors(), 'updateMeja')
                ->withInput()
                ->with('meja_id_on_error', $meja->id); // Kirim ID meja agar JS tahu modal mana yang harus dibuka
        } catch (\Exception $e) {
            Log::error('Error updating meja: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui meja. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meja $meja) // Menggunakan Route Model Binding
    {
        // Pastikan meja tidak sedang dipakai sebelum dihapus
        if ($meja->status == 'dipakai') {
            return redirect()->route('admin.mejas.index')->with('error', 'Meja tidak bisa dihapus karena sedang dipakai.');
        }

        try {
            $meja->delete();
            return redirect()->route('admin.mejas.index')->with('success', 'Meja berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting meja: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus meja. Silakan coba lagi.');
        }
    }
}