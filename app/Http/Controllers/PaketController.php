<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::orderBy('created_at', 'desc')->paginate(10);
        $services = Service::select('id', 'nama', 'harga', 'stok')->orderBy('nama')->get();
        return view('admin.pakets.index', compact('pakets', 'services'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket',
                'harga_paket' => 'required|numeric|min:0',
                'durasi_jam' => 'required|numeric|min:0',
                'deskripsi_tambahan' => 'nullable|string',
                'aktif' => 'boolean',
                'services' => 'nullable|array',
                'services.*.id' => 'required_with:services|exists:services,id',
                'services.*.jumlah' => 'required_with:services|integer|min:0',
            ], [
                'nama_paket.required' => 'Nama paket wajib diisi.',
                'nama_paket.unique' => 'Nama paket ini sudah ada.',
                'harga_paket.required' => 'Harga paket wajib diisi.',
                'durasi_jam.required' => 'Durasi jam wajib diisi.',
            ]);

            $serviceDetails = [];
            if ($request->has('services')) {
                foreach ($request->services as $serviceItem) {
                    if (isset($serviceItem['jumlah']) && $serviceItem['jumlah'] > 0) {
                        $service = Service::find($serviceItem['id']);
                        if ($service) {
                            $serviceDetails[] = [
                                'id' => (int)$service->id,
                                'nama' => $service->nama,
                                'jumlah' => (int)$serviceItem['jumlah'],
                                'subtotal' => $service->harga * (int)$serviceItem['jumlah'],
                            ];
                        }
                    }
                }
            }

            $isiPaket = [
                'harga_paket' => (float)$request->harga_paket,
                'durasi_jam' => (float)$request->durasi_jam,
                'deskripsi_tambahan' => $request->deskripsi_tambahan,
                'services' => $serviceDetails,
            ];

            Paket::create([
                'nama_paket' => $request->nama_paket,
                'isi_paket' => $isiPaket,
                'aktif' => $request->boolean('aktif'),
            ]);

            return redirect()->route('admin.pakets.index')->with('success', 'Paket berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'storePaket')->withInput();
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menambahkan paket. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Paket $paket)
    {
        try {
            $request->validate([
                'nama_paket' => ['required', 'string', 'max:255', Rule::unique('pakets')->ignore($paket->id)],
                'harga_paket' => 'required|numeric|min:0',
                'durasi_jam' => 'required|numeric|min:0',
                'deskripsi_tambahan' => 'nullable|string',
                'aktif' => 'boolean',
                'services' => 'nullable|array',
                'services.*.id' => 'required_with:services|exists:services,id',
                'services.*.jumlah' => 'required_with:services|integer|min:0',
            ], [
                'nama_paket.required' => 'Nama paket wajib diisi.',
                'nama_paket.unique' => 'Nama paket ini sudah ada.',
                'harga_paket.required' => 'Harga paket wajib diisi.',
                'durasi_jam.required' => 'Durasi jam wajib diisi.',
            ]);

            $serviceDetails = [];
            if ($request->has('services')) {
                foreach ($request->services as $serviceItem) {
                    if (isset($serviceItem['jumlah']) && $serviceItem['jumlah'] > 0) {
                        $service = Service::find($serviceItem['id']);
                        if ($service) {
                            $serviceDetails[] = [
                                'id' => (int)$service->id,
                                'nama' => $service->nama,
                                'jumlah' => (int)$serviceItem['jumlah'],
                                'subtotal' => $service->harga * (int)$serviceItem['jumlah'],
                            ];
                        }
                    }
                }
            }

            $isiPaket = [
                'harga_paket' => (float)$request->harga_paket,
                'durasi_jam' => (float)$request->durasi_jam,
                'deskripsi_tambahan' => $request->deskripsi_tambahan,
                'services' => $serviceDetails,
            ];

            $paket->update([
                'nama_paket' => $request->nama_paket,
                'isi_paket' => $isiPaket,
                'aktif' => $request->boolean('aktif'),
            ]);

            // NEW: Return JSON response for AJAX
            return response()->json(['message' => 'Paket berhasil diperbarui!', 'paket' => $paket]);
        } catch (ValidationException $e) {
            // NEW: Return JSON error response for AJAX
            return response()->json(['errors' => $e->errors(), 'message' => 'Validasi gagal!'], 422);
        } catch (\Exception $e) {
            // NEW: Return JSON error response for AJAX
            return response()->json(['message' => 'Gagal memperbarui paket. Silakan coba lagi.'], 500);
        }
    }

    public function destroy(Paket $paket)
    {
        try {
            $paket->delete();
            return redirect()->route('admin.pakets.index')->with('success', 'Paket berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus paket. Silakan coba lagi.');
        }
    }

    public function getPaketsJson()
    {
        $pakets = Paket::where('aktif', true)
                        ->select('id', 'nama_paket', 'isi_paket')
                        ->orderBy('nama_paket')
                        ->get();
        return response()->json($pakets);
    }
}