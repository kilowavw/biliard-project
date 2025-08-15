<?php

namespace App\Http\Controllers;

use App\Models\Kupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class KuponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kupons = Kupon::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.kupons.index', compact('kupons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'kode' => 'required|string|max:255|unique:kupons,kode',
                'diskon_persen' => 'required|numeric|min:1|max:100',
                'aktif' => 'boolean',
                'kadaluarsa' => 'nullable|date|after_or_equal:today',
            ], [
                'kode.required' => 'Kode kupon wajib diisi.',
                'kode.unique' => 'Kode kupon ini sudah ada.',
                'diskon_persen.required' => 'Diskon persentase wajib diisi.',
                'diskon_persen.numeric' => 'Diskon persentase harus angka.',
                'diskon_persen.min' => 'Diskon minimal 1%.',
                'diskon_persen.max' => 'Diskon maksimal 100%.',
                'kadaluarsa.date' => 'Tanggal kadaluarsa tidak valid.',
                'kadaluarsa.after_or_equal' => 'Tanggal kadaluarsa tidak boleh di masa lalu.',
            ]);

            Kupon::create([
                'kode' => strtoupper($request->kode), // Simpan kode dalam huruf besar
                'diskon_persen' => $request->diskon_persen,
                'aktif' => $request->boolean('aktif'),
                'kadaluarsa' => $request->kadaluarsa,
            ]);

            return redirect()->route('admin.kupons.index')->with('success', 'Kupon berhasil ditambahkan!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors(), 'storeKupon')->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing kupon: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal menambahkan kupon. Silakan coba lagi.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kupon $kupon)
    {
        try {
            $request->validate([
                'kode' => ['required', 'string', 'max:255', Rule::unique('kupons')->ignore($kupon->id)],
                'diskon_persen' => 'required|numeric|min:1|max:100',
                'aktif' => 'boolean',
                'kadaluarsa' => 'nullable|date|after_or_equal:today',
            ], [
                'kode.required' => 'Kode kupon wajib diisi.',
                'kode.unique' => 'Kode kupon ini sudah ada.',
                'diskon_persen.required' => 'Diskon persentase wajib diisi.',
                'diskon_persen.numeric' => 'Diskon persentase harus angka.',
                'diskon_persen.min' => 'Diskon minimal 1%.',
                'diskon_persen.max' => 'Diskon maksimal 100%.',
                'kadaluarsa.date' => 'Tanggal kadaluarsa tidak valid.',
                'kadaluarsa.after_or_equal' => 'Tanggal kadaluarsa tidak boleh di masa lalu.',
            ]);

            $kupon->update([
                'kode' => strtoupper($request->kode),
                'diskon_persen' => $request->diskon_persen,
                'aktif' => $request->boolean('aktif'),
                'kadaluarsa' => $request->kadaluarsa,
            ]);

            return redirect()->route('admin.kupons.index')->with('success', 'Kupon berhasil diperbarui!');
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors(), 'updateKupon')
                ->withInput()
                ->with('kupon_id_on_error', $kupon->id);
        } catch (\Exception $e) {
            Log::error('Error updating kupon: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memperbarui kupon. Silakan coba lagi.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kupon $kupon)
    {
        try {
            $kupon->delete();
            return redirect()->route('admin.kupons.index')->with('success', 'Kupon berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting kupon: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus kupon. Silakan coba lagi.');
        }
    }

    /**
     * API for validating a coupon code.
     * Used by frontend for real-time coupon validation.
     */
    public function validateKupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:255']);

        $kupon = Kupon::where('kode', strtoupper($request->code)) // Case-insensitive check
                      ->where('aktif', true)
                      ->where(function($query) {
                          $query->whereNull('kadaluarsa')
                                ->orWhere('kadaluarsa', '>=', now());
                      })
                      ->first();

        if ($kupon) {
            return response()->json(['valid' => true, 'diskon_persen' => $kupon->diskon_persen]);
        }
        return response()->json(['valid' => false, 'message' => 'Kupon tidak valid atau sudah kadaluarsa.'], 404);
    }
}