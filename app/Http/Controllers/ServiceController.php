<?php

// File: app/Http/Controllers/ServiceController.php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('nama')->paginate(10);
        $categories = ['alat', 'makanan', 'minuman'];
        return view('admin.services.index', compact('services', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama' => 'required|string|max:255|unique:services,nama',
                'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman'])],
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
            ], [
                'nama.required' => 'Nama service wajib diisi.',
                'nama.string' => 'Nama service harus berupa teks.',
                'nama.max' => 'Nama service tidak boleh lebih dari :max karakter.',
                'nama.unique' => 'Nama service ini sudah ada, silakan gunakan nama lain.',
                'kategori.required' => 'Kategori wajib dipilih.',
                'kategori.in' => 'Kategori tidak valid.',
                'harga.required' => 'Harga wajib diisi.',
                'harga.numeric' => 'Harga harus berupa angka.',
                'harga.min' => 'Harga tidak boleh negatif.',
                'stok.required' => 'Stok wajib diisi.',
                'stok.integer' => 'Stok harus berupa bilangan bulat.',
                'stok.min' => 'Stok tidak boleh negatif.',
            ]);

            Service::create($request->all());

            return redirect()->route('admin.services.index')->with('success', 'Service berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'storeService')->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing service: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan service. Silakan coba lagi.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        try {
            $request->validate([
                'nama' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
                'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman'])],
                'harga' => 'required|numeric|min:0',
                'stok' => 'required|integer|min:0',
            ], [
                'nama.required' => 'Nama service wajib diisi.',
                'nama.string' => 'Nama service harus berupa teks.',
                'nama.max' => 'Nama service tidak boleh lebih dari :max karakter.',
                'nama.unique' => 'Nama service ini sudah ada, silakan gunakan nama lain.',
                'kategori.required' => 'Kategori wajib dipilih.',
                'kategori.in' => 'Kategori tidak valid.',
                'harga.required' => 'Harga wajib diisi.',
                'harga.numeric' => 'Harga harus berupa angka.',
                'harga.min' => 'Harga tidak boleh negatif.',
                'stok.required' => 'Stok wajib diisi.',
                'stok.integer' => 'Stok harus berupa bilangan bulat.',
                'stok.min' => 'Stok tidak boleh negatif.',
            ]);

            $service->update($request->all());

            return redirect()->route('admin.services.index')->with('success', 'Service berhasil diperbarui!');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors(), 'updateService')
                ->withInput()
                ->with('service_id_on_error', $service->id);
        } catch (\Exception $e) {
            Log::error('Error updating service: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui service. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return redirect()->route('admin.services.index')->with('success', 'Service berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus service. Silakan coba lagi.');
        }
    }

    /**
     * Get all services for API consumption.
     */
    public function getServicesJson()
    {
        $services = Service::select('id', 'nama', 'harga', 'stok', 'kategori')->orderBy('nama')->get();
        return response()->json($services);
    }
}