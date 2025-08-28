<?php

// File: app/Http/Controllers/ServiceController.php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Kupon; // Import Kupon
use App\Models\ServiceTransaction; // Import ServiceTransaction
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Untuk transaksi database
use Illuminate\Support\Facades\Auth; // Untuk kasir_id

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::orderBy('nama')->paginate(10);
        $categories = ['alat', 'makanan', 'minuman', 'rokok'];
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
                'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman', 'rokok'])],
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
                'kategori' => ['required', Rule::in(['alat', 'makanan', 'minuman','rokok'])],
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
    
  /**
     * NEW: Display the form for direct service ordering by Kasir.
     */
       public function kasirServiceOrderIndex()
    {
        // For the order modal, we just need active services with stock > 0.
        // We will fetch all services via API in JS to get the current stock.
        
        // For history, fetch latest transactions by the current kasir
        $serviceTransactions = ServiceTransaction::where('kasir_id', Auth::id())
                                                ->orderBy('transaction_time', 'desc')
                                                ->paginate(10); // Histori transaksi
        
        // Pass server time for JS calibration (similar to kasir dashboard)
        $serverTime = now()->toIso8601String();

        return view('kasir.services.index', compact('serviceTransactions', 'serverTime'));
    }


    /**
     * NEW: Process a direct service order from Kasir.
     */
    public function processServiceOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'services' => 'required|array|min:1',
            'services.*.id' => 'required|exists:services,id',
            'services.*.jumlah' => 'required|integer|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'qris', 'pay_later'])],
            'kode_kupon' => 'nullable|string|max:255',
        ], [
            'services.required' => 'Pilih setidaknya satu layanan.',
            'services.min' => 'Pilih setidaknya satu layanan.',
            'services.*.jumlah.min' => 'Jumlah layanan harus minimal 1.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        DB::beginTransaction();
        try {
            $orderedServiceDetails = [];
            $totalServiceCost = 0;

            foreach ($request->services as $serviceItem) {
                // Ensure correct variable names match the data passed.
                $service = Service::lockForUpdate()->find($serviceItem['id']);
                if (!$service) {
                    DB::rollBack();
                    // Better error message
                    throw new \Exception("Service dengan ID {$serviceItem['id']} tidak ditemukan.");
                }

                if ($service->stok < $serviceItem['jumlah']) {
                    DB::rollBack();
                    throw new \Exception("Stok {$service->nama} tidak cukup. Tersedia: {$service->stok}, diminta: {$serviceItem['jumlah']}.");
                }

                $subtotal = $service->harga * $serviceItem['jumlah'];
                $totalServiceCost += $subtotal;

                $orderedServiceDetails[] = [
                    'id' => (int)$service->id,
                    'nama' => $service->nama,
                    'jumlah' => (int)$serviceItem['jumlah'],
                    'harga_satuan' => (float)$service->harga,
                    'subtotal' => (float)$subtotal,
                ];

                $service->decrement('stok', $serviceItem['jumlah']);
            }

            $diskonPersen = 0;
            $diskonAmount = 0;
            $kodeKuponDigunakan = null;

            if ($request->filled('kode_kupon')) {
                // Fetch coupon details directly or re-use existing KuponController logic carefully
                $kupon = Kupon::where('kode', $request->kode_kupon)
                                ->where('aktif', true)
                                ->where(function($query) {
                                    $query->whereNull('kadaluarsa')
                                        ->orWhere('kadaluarsa', '>=', now());
                                })
                                ->first();

                if ($kupon) {
                    $diskonPersen = (float)$kupon->diskon_persen;
                    $kodeKuponDigunakan = $request->kode_kupon;
                } else {
                    DB::rollBack();
                    throw new \Exception('Kupon tidak valid atau sudah kadaluarsa.');
                }
            }

            $totalBeforeDiscount = $totalServiceCost;
            $diskonAmount = ($totalBeforeDiscount * $diskonPersen) / 100;
            $totalBayarAkhir = $totalBeforeDiscount - $diskonAmount;

            $paymentStatus = 'paid';
            if ($request->payment_method === 'pay_later') {
                $paymentStatus = 'pending';
            }

            $serviceTransaction = ServiceTransaction::create([
                'customer_name' => $request->customer_name,
                'kasir_id' => Auth::id(),
                'total_service' => $totalServiceCost,
                'service_detail' => json_encode($orderedServiceDetails),
                'kode_kupon' => $kodeKuponDigunakan,
                'diskon_persen' => $diskonPersen,
                'diskon_amount' => $diskonAmount,
                'total_bayar' => $totalBayarAkhir,
                'payment_method' => $request->payment_method,
                'payment_status' => $paymentStatus,
                'transaction_time' => now(),
            ]);

            DB::commit();

            $successMessage = "Pemesanan service sukses!";
            if ($serviceTransaction->payment_status === 'pending') {
                $successMessage .= " Transaksi #${serviceTransaction->id} disimpan dengan status menunggu pembayaran.";
            } else {
                $successMessage .= " Total bayar: " . number_format($serviceTransaction->total_bayar, 0, ',', '.');
            }

            // Return a more robust JSON response. Ensure all values are scalar.
            return response()->json([
                'message' => $successMessage,
                'transaction_id' => (int)$serviceTransaction->id,
                'total_bayar' => (float)$serviceTransaction->total_bayar,
                'payment_status' => $paymentStatus
            ], 200);

        } catch (\Exception $e) { // Catch all general exceptions
            DB::rollBack();
            Log::error('Error processing service order: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'request_payload' => $request->all()]); // Log more details
            // For general exceptions, return 500 but still catch custom validation exceptions in frontend.
            return response()->json(['message' => $e->getMessage() . '. Gagal memproses pesanan service. Silakan coba lagi.'], 500);
        }
    }

    // NEW: Metode untuk update status pembayaran transaksi service
    public function updateServiceTransactionPaymentStatus(Request $request, ServiceTransaction $transaction)
    {
        // Hanya izinkan kasir update transaksi sendiri yang pending
        // matikan ngebug
        // if ($transaction->kasir_id !== Auth::id()) {
        //      return response()->json(['message' => 'Anda tidak berwenang melakukan tindakan ini.'], 403);
        // }
        if ($transaction->payment_status !== 'pending') {
             return response()->json(['message' => 'Status transaksi ini tidak bisa diubah.'], 400);
        }

        $request->validate([
            'payment_method' => ['required', Rule::in(['cash', 'qris'])],
            'payment_status' => ['required', Rule::in(['paid'])],
        ]);

        $transaction->update([
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            // 'transaction_time' => now(), // Optional: Update transaction time to payment time
        ]);

        return response()->json(['message' => 'Status pembayaran berhasil diperbarui!', 'transaction' => $transaction->fresh()]);
    }
}