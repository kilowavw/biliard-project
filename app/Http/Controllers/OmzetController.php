<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use App\Models\ServiceTransaction;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OmzetController extends Controller
{
    const REPORT_START_HOUR = 2; // Sama seperti di LaporanController

    // Fungsi bantuan untuk menyesuaikan tanggal
    private function adjustDateForReport(Carbon $originalTime): Carbon
    {
        if ($originalTime->hour < self::REPORT_START_HOUR) {
            return $originalTime->copy()->subDay()->startOfDay();
        }
        return $originalTime->copy()->startOfDay();
    }

    public function generateReport(Request $request)
    {
        // 1. Ambil rentang tanggal dari request (ini adalah rentang yang TAMPIL)
        $startDateInput = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfDay();
        $endDateInput = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();

        // 2. Hitung rentang waktu yang benar-benar digunakan untuk query database berdasarkan jam rekap
        // Kita perlu mengambil data dari 02:00 WIB tanggal mulai hingga 01:59:59 WIB tanggal berikutnya dari endDateInput
        $queryStartDate = $startDateInput->copy()->addHours(self::REPORT_START_HOUR);
        $queryEndDate = $endDateInput->copy()->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

        // Inisialisasi variabel untuk menyimpan total omzet per kategori dan metode pembayaran
        $report = [
            'Billiard' => ['Cash' => 0, 'QRIS' => 0],
            'minuman' => ['Cash' => 0, 'QRIS' => 0],
            'makanan' => ['Cash' => 0, 'QRIS' => 0],
            'rokok' => ['Cash' => 0, 'QRIS' => 0],
            'alat' => ['Cash' => 0, 'QRIS' => 0],
        ];

        $totalOverallCash = 0;
        $totalOverallQris = 0;
        $totalOverallOmzet = 0;

        // 3. Ambil daftar kategori layanan dari tabel Service
        $services = Service::all()->keyBy('id');

        // 4. Ambil semua data Penyewaan dan ServiceTransaction dalam rentang query
        $penyewaans = Penyewaan::whereBetween('waktu_selesai', [$queryStartDate, $queryEndDate])
                               ->where('status', 'dibayar')
                               ->get();

        $serviceTransactions = ServiceTransaction::whereBetween('transaction_time', [$queryStartDate, $queryEndDate])
                                               ->where('payment_status', 'paid')
                                               ->get();

        // 5. Filter dan proses data Penyewaan berdasarkan tanggal laporan yang disesuaikan
        foreach ($penyewaans as $penyewaan) {
            // Dapatkan tanggal laporan yang disesuaikan untuk transaksi ini
            $reportDate = $this->adjustDateForReport(Carbon::parse($penyewaan->waktu_selesai));

            // Periksa apakah transaksi ini termasuk dalam rentang tanggal input (tanggal tampil)
            if ($reportDate->between($startDateInput->startOfDay(), $endDateInput->endOfDay())) {
                $paymentMethod = $penyewaan->is_qris ? 'QRIS' : 'Cash';

                $omzetBilliardMurni = 0;
                if ($penyewaan->total_bayar !== null) {
                    $omzetBilliardMurni = $penyewaan->total_bayar - $penyewaan->total_service;
                } else {
                    $basePrice = $penyewaan->durasi_jam * $penyewaan->harga_per_jam;
                    $discountAmount = $basePrice * ($penyewaan->diskon_member_persen / 100);
                    $omzetBilliardMurni = $basePrice - $discountAmount - $penyewaan->total_service;
                }

                $report['Billiard'][$paymentMethod] += $omzetBilliardMurni;
                if ($paymentMethod === 'QRIS') {
                    $totalOverallQris += $omzetBilliardMurni;
                } else {
                    $totalOverallCash += $omzetBilliardMurni;
                }

                $serviceDetails = [];
                if (is_string($penyewaan->service_detail)) {
                    $serviceDetails = json_decode($penyewaan->service_detail, true);
                } elseif (is_array($penyewaan->service_detail)) {
                    $serviceDetails = $penyewaan->service_detail;
                }

                if ($serviceDetails) {
                    foreach ($serviceDetails as $serviceItem) {
                        if (isset($serviceItem['id']) && isset($serviceItem['subtotal'])) {
                            $serviceId = $serviceItem['id'];
                            $subtotal = $serviceItem['subtotal'];

                            if (isset($services[$serviceId])) {
                                $category = $services[$serviceId]->kategori;
                                if (isset($report[$category])) {
                                    $report[$category][$paymentMethod] += $subtotal;
                                    if ($paymentMethod === 'QRIS') {
                                        $totalOverallQris += $subtotal;
                                    } else {
                                        $totalOverallCash += $subtotal;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // 6. Filter dan proses data ServiceTransaction berdasarkan tanggal laporan yang disesuaikan
        foreach ($serviceTransactions as $transaction) {
            // Dapatkan tanggal laporan yang disesuaikan untuk transaksi ini
            $reportDate = $this->adjustDateForReport(Carbon::parse($transaction->transaction_time));

            // Periksa apakah transaksi ini termasuk dalam rentang tanggal input (tanggal tampil)
            if ($reportDate->between($startDateInput->startOfDay(), $endDateInput->endOfDay())) {
                $paymentMethod = (strtolower($transaction->payment_method) === 'qris') ? 'QRIS' : 'Cash';
                
                $serviceDetails = [];
                if (is_string($transaction->service_detail)) {
                    $serviceDetails = json_decode($transaction->service_detail, true);
                } elseif (is_array($transaction->service_detail)) {
                    $serviceDetails = $transaction->service_detail;
                }

                if ($serviceDetails) {
                    foreach ($serviceDetails as $serviceItem) {
                        if (isset($serviceItem['id']) && (isset($serviceItem['harga_satuan']) || isset($serviceItem['subtotal'])) && isset($serviceItem['jumlah'])) {
                            $serviceId = $serviceItem['id'];
                            $subtotal = ($serviceItem['subtotal'] ?? ($serviceItem['harga_satuan'] * $serviceItem['jumlah']));

                            if (isset($services[$serviceId])) {
                                $category = $services[$serviceId]->kategori;
                                if (isset($report[$category])) {
                                    $report[$category][$paymentMethod] += $subtotal;
                                    if ($paymentMethod === 'QRIS') {
                                        $totalOverallQris += $subtotal;
                                    } else {
                                        $totalOverallCash += $subtotal;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // 7. Hitung total omzet keseluruhan untuk rentang tanggal input
        $totalOverallOmzet = $totalOverallCash + $totalOverallQris;

        // --- Bagian BARU: Hitung omzet dari awal bulan hingga endDateInput ---
        // Kita hitung dari awal bulan input hingga akhir tanggal input, sesuai logika rekap
        $firstDayOfMonthInput = $endDateInput->copy()->startOfMonth();
        $totalOmzetMonthToDate = 0;

        // Hitung rentang query untuk periode awal bulan hingga akhir tanggal input
        $queryStartOfMonth = $firstDayOfMonthInput->copy()->addHours(self::REPORT_START_HOUR);
        $queryEndOfMonth = $endDateInput->copy()->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

        // Ambil data Penyewaan untuk periode awal bulan hingga akhir tanggal input
        $penyewaansMonthToDate = Penyewaan::whereBetween('waktu_selesai', [$queryStartOfMonth, $queryEndOfMonth])
                                          ->where('status', 'dibayar')
                                          ->get();

        foreach ($penyewaansMonthToDate as $penyewaan) {
            // Filter berdasarkan tanggal laporan yang disesuaikan
            $reportDate = $this->adjustDateForReport(Carbon::parse($penyewaan->waktu_selesai));
            if ($reportDate->between($firstDayOfMonthInput->startOfDay(), $endDateInput->endOfDay())) {
                $omzetBilliardMurni = 0;
                if ($penyewaan->total_bayar !== null) {
                    $omzetBilliardMurni = $penyewaan->total_bayar - $penyewaan->total_service;
                } else {
                    $basePrice = $penyewaan->durasi_jam * $penyewaan->harga_per_jam;
                    $discountAmount = $basePrice * ($penyewaan->diskon_member_persen / 100);
                    $omzetBilliardMurni = $basePrice - $discountAmount - $penyewaan->total_service;
                }
                $totalOmzetMonthToDate += $omzetBilliardMurni;

                $serviceDetails = [];
                if (is_string($penyewaan->service_detail)) {
                    $serviceDetails = json_decode($penyewaan->service_detail, true);
                } elseif (is_array($penyewaan->service_detail)) {
                    $serviceDetails = $penyewaan->service_detail;
                }
                if ($serviceDetails) {
                    foreach ($serviceDetails as $serviceItem) {
                        if (isset($serviceItem['id']) && isset($serviceItem['subtotal'])) {
                            $totalOmzetMonthToDate += $serviceItem['subtotal'];
                        }
                    }
                }
            }
        }

        // Ambil data ServiceTransaction untuk periode awal bulan hingga akhir tanggal input
        $serviceTransactionsMonthToDate = ServiceTransaction::whereBetween('transaction_time', [$queryStartOfMonth, $queryEndOfMonth])
                                                           ->where('payment_status', 'paid')
                                                           ->get();

        foreach ($serviceTransactionsMonthToDate as $transaction) {
            // Filter berdasarkan tanggal laporan yang disesuaikan
            $reportDate = $this->adjustDateForReport(Carbon::parse($transaction->transaction_time));
            if ($reportDate->between($firstDayOfMonthInput->startOfDay(), $endDateInput->endOfDay())) {
                $serviceDetails = [];
                if (is_string($transaction->service_detail)) {
                    $serviceDetails = json_decode($transaction->service_detail, true);
                } elseif (is_array($transaction->service_detail)) {
                    $serviceDetails = $transaction->service_detail;
                }
                if ($serviceDetails) {
                    foreach ($serviceDetails as $serviceItem) {
                        if (isset($serviceItem['id']) && (isset($serviceItem['harga_satuan']) || isset($serviceItem['subtotal'])) && isset($serviceItem['jumlah'])) {
                            $subtotal = ($serviceItem['subtotal'] ?? ($serviceItem['harga_satuan'] * $serviceItem['jumlah']));
                            $totalOmzetMonthToDate += $subtotal;
                        }
                    }
                }
            }
        }
        // --- Akhir Bagian BARU ---

        // Mengirimkan data ke view
        return view('laporan.omzet', [
            'reportData' => $report,
            'startDate' => $startDateInput,
            'endDate' => $endDateInput,
            'totalOverallCash' => $totalOverallCash,
            'totalOverallQris' => $totalOverallQris,
            'totalOverallOmzet' => $totalOverallOmzet,
            'totalOmzetRangePeriod' => $totalOmzetMonthToDate,
            'firstDayOfMonth' => $firstDayOfMonthInput
        ]);
    }
}