<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan harian data penyewaan dengan pagination untuk detail transaksi.
     */
    public function harian(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // Ambil SEMUA data penyewaan untuk hari tersebut untuk menghitung agregat
        $penyewaansCollection = Penyewaan::with('meja')
                                          ->whereDate('waktu_mulai', $date)
                                          ->orderBy('waktu_mulai', 'asc')
                                          ->get();

        // 1. Total Pendapatan (Nett)
        $totalPendapatan = $penyewaansCollection->sum('total_bayar');

        // 2. Distribusi Pendapatan (Cash vs QRIS)
        $qrisTotal = $penyewaansCollection->where('is_qris', true)->sum('total_bayar');
        $cashTotal = $penyewaansCollection->where('is_qris', false)->sum('total_bayar');

        $paymentMethodDistribution = [];
        if ($cashTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal];
        }
        if ($qrisTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal];
        }
        // Tambahkan kondisi jika tidak ada data sama sekali agar chart tidak error
        if (empty($paymentMethodDistribution)) {
            $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1];
        }

        // 3. Pendapatan Per Meja
        $pendapatanPerMeja = $penyewaansCollection->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Data detail penyewaan dengan pagination
        $penyewaansPaginated = Penyewaan::with('meja')
                                        ->whereDate('waktu_mulai', $date)
                                        ->orderBy('waktu_mulai', 'desc') // Urutkan terbaru dulu
                                        ->paginate(10); // Atur jumlah item per halaman, misalnya 10

        return view('laporan.harian', compact(
            'date',
            'totalPendapatan',
            'paymentMethodDistribution',
            'pendapatanPerMeja',
            'penyewaansPaginated' // Menggunakan data yang sudah di-paginate untuk tabel detail
        ));
    }

    /**
     * Menampilkan laporan bulanan data penyewaan dengan pagination untuk detail transaksi.
     */
    public function bulanan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n')); // 'n' untuk bulan tanpa leading zero (1-12)

        // --- Data untuk Agregasi Grafik (Total Pendapatan Per Hari) ---
        $monthlyRevenue = Penyewaan::select(
                                DB::raw('DAY(waktu_mulai) as day'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereMonth('waktu_mulai', $month)
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('day')
                            ->orderBy('day', 'asc')
                            ->get();

        $chartLabelsDaily = [];
        $chartDataDaily = [];

        // Inisialisasi label dan data untuk semua hari dalam bulan
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabelsDaily[] = $i; // Label hari: 1, 2, 3, dst.
            $chartDataDaily[$i] = 0; // Default ke 0 jika tidak ada penjualan di hari tersebut
        }

        foreach ($monthlyRevenue as $data) {
            $chartDataDaily[$data->day] = $data->total;
        }
        $chartDataDaily = array_values($chartDataDaily); // Reset keys untuk array JavaScript

        // --- Data untuk Ringkasan Bulanan (Total Pendapatan, Distribusi Pembayaran, Pendapatan Per Meja) ---
        // Ambil SEMUA data penyewaan untuk bulan tersebut untuk menghitung agregat
        $penyewaansCollection = Penyewaan::with('meja')
                                          ->whereMonth('waktu_mulai', $month)
                                          ->whereYear('waktu_mulai', $year)
                                          ->get();

        $totalPendapatan = $penyewaansCollection->sum('total_bayar');

        $qrisTotal = $penyewaansCollection->where('is_qris', true)->sum('total_bayar');
        $cashTotal = $penyewaansCollection->where('is_qris', false)->sum('total_bayar');

        $paymentMethodDistribution = [];
        if ($cashTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal];
        }
        if ($qrisTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal];
        }
        if (empty($paymentMethodDistribution)) {
            $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1];
        }

        $pendapatanPerMeja = $penyewaansCollection->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Data detail penyewaan dengan pagination
        $penyewaansPaginated = Penyewaan::with('meja')
                                ->whereMonth('waktu_mulai', $month)
                                ->whereYear('waktu_mulai', $year)
                                ->orderBy('waktu_mulai', 'desc')
                                ->paginate(10); // Paginate the detail table

        // Untuk dropdown filter bulan/tahun
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 10)); // Mendapatkan nama bulan
        }
        $years = range(date('Y') - 5, date('Y') + 1); // Contoh rentang tahun

        return view('laporan.bulanan', compact(
            'penyewaansPaginated', // Menggunakan data yang sudah di-paginate
            'totalPendapatan',
            'paymentMethodDistribution',
            'pendapatanPerMeja',
            'chartLabelsDaily', // Untuk grafik bar ApexCharts (Daily Revenue)
            'chartDataDaily',   // Untuk grafik bar ApexCharts (Daily Revenue)
            'year',
            'month',
            'months',
            'years'
        ));
    }

    /**
     * Menampilkan laporan tahunan yang diagregasi per bulan dengan pagination untuk detail transaksi.
     */
    public function tahunan(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // --- Data untuk Grafik "Total Pendapatan Per Bulan" (ApexCharts Bar Chart) ---
        $monthlyRevenueRaw = Penyewaan::select(
                                DB::raw('MONTH(waktu_mulai) as month_num'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('month_num')
                            ->orderBy('month_num', 'asc')
                            ->get();

        $chartLabelsMonthly = []; // Untuk nama bulan
        $chartDataMonthly = [];   // Untuk total pendapatan per bulan

        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1)); // Mendapatkan nama bulan (e.g., January)
            $chartLabelsMonthly[] = $monthName;
            $chartDataMonthly[$i] = 0; // Default ke 0 jika tidak ada penjualan di bulan tersebut
        }

        foreach ($monthlyRevenueRaw as $data) {
            $chartDataMonthly[$data->month_num] = $data->total;
        }
        $chartDataMonthly = array_values($chartDataMonthly); // Reset keys untuk array JavaScript

        // --- Data untuk Ringkasan Tahunan (Total Pendapatan, Distribusi Pembayaran, Pendapatan Per Meja) ---
        // Ambil SEMUA data penyewaan untuk tahun tersebut untuk menghitung agregat
        $penyewaansCollection = Penyewaan::with('meja')
                                          ->whereYear('waktu_mulai', $year)
                                          ->get(); // Get all for aggregates

        $totalPendapatan = $penyewaansCollection->sum('total_bayar');

        $qrisTotal = $penyewaansCollection->where('is_qris', true)->sum('total_bayar');
        $cashTotal = $penyewaansCollection->where('is_qris', false)->sum('total_bayar');

        $paymentMethodDistribution = [];
        if ($cashTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal];
        }
        if ($qrisTotal > 0) {
            $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal];
        }
        if (empty($paymentMethodDistribution)) {
            $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1];
        }

        $pendapatanPerMeja = $penyewaansCollection->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Data detail penyewaan dengan pagination
        $penyewaansPaginated = Penyewaan::with('meja')
                                        ->whereYear('waktu_mulai', $year)
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10); // Paginate the detail table

        // Untuk dropdown filter tahun
        $years = range(date('Y') - 5, date('Y') + 1); // Contoh rentang tahun

        return view('laporan.tahunan', compact(
            'penyewaansPaginated', // Menggunakan data yang sudah di-paginate
            'totalPendapatan',
            'paymentMethodDistribution',
            'pendapatanPerMeja',
            'chartLabelsMonthly', // Untuk grafik bar ApexCharts (Monthly Revenue)
            'chartDataMonthly',   // Untuk grafik bar ApexCharts (Monthly Revenue)
            'year',
            'years'
        ));
    }
}