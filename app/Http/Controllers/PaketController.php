<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Service; // Masih diperlukan untuk menampilkan daftar service di form
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::orderBy('created_at', 'desc')->paginate(10);
        // Kita tidak lagi butuh $services di sini untuk form paket,
        // karena service akan dimasukkan manual dalam JSON 'isi_paket'.
        // Namun, jika Anda punya halaman manajemen service yang terpisah, ini bisa dihapus.
        // Untuk saat ini, kita biarkan saja agar modal tambah/edit paket bisa memuat daftar service untuk contoh JSON.
        $services = Service::select('id', 'nama', 'harga', 'stok')->orderBy('nama')->get();

        return view('admin.pakets.index', compact('pakets', 'services'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket',
                'isi_paket_json' => 'required|string|json', // Validasi bahwa ini adalah string JSON yang valid
                'aktif' => 'boolean',
            ], [
                'nama_paket.required' => 'Nama paket wajib diisi.',
                'nama_paket.unique' => 'Nama paket ini sudah ada.',
                'isi_paket_json.required' => 'Isi paket (JSON) wajib diisi.',
                'isi_paket_json.json' => 'Isi paket harus berupa format JSON yang valid.',
            ]);

            Paket::create([
                'nama_paket' => $request->nama_paket,
                'isi_paket' => $request->isi_paket_json, // Simpan langsung string JSON dari form
                'aktif' => $request->boolean('aktif'),
            ]);

            return redirect()->route('admin.pakets.index')->with('success', 'Paket berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'storePaket')->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing paket: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan paket. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Paket $paket)
    {
        try {
            $request->validate([
                'nama_paket' => ['required', 'string', 'max:255', Rule::unique('pakets')->ignore($paket->id)],
                'isi_paket_json' => 'required|string|json', // Validasi bahwa ini adalah string JSON yang valid
                'aktif' => 'boolean',
            ], [
                'nama_paket.required' => 'Nama paket wajib diisi.',
                'nama_paket.unique' => 'Nama paket ini sudah ada.',
                'isi_paket_json.required' => 'Isi paket (JSON) wajib diisi.',
                'isi_paket_json.json' => 'Isi paket harus berupa format JSON yang valid.',
            ]);

            $paket->update([
                'nama_paket' => $request->nama_paket,
                'isi_paket' => $request->isi_paket_json, // Simpan langsung string JSON dari form
                'aktif' => $request->boolean('aktif'),
            ]);

            return redirect()->route('admin.pakets.index')->with('success', 'Paket berhasil diperbarui!');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors(), 'updatePaket')
                ->withInput()
                ->with('paket_id_on_error', $paket->id);
        } catch (\Exception $e) {
            Log::error('Error updating paket: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui paket. Silakan coba lagi.');
        }
    }

    public function destroy(Paket $paket)
    {
        try {
            $paket->delete();
            return redirect()->route('admin.pakets.index')->with('success', 'Paket berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting paket: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus paket. Silakan coba lagi.');
        }
    }

    /**
     * Get all active pakets for API consumption by kasir dashboard.
     */
    public function getPaketsJson()
    {
        $pakets = Paket::where('aktif', true)
                        ->select('id', 'nama_paket', 'isi_paket') // Hanya kirim yang relevan
                        ->orderBy('nama_paket')
                        ->get();
        return response()->json($pakets);
    }
}