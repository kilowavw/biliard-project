<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Menampilkan laporan harian data penyewaan.
     * Termasuk data tabel dan grafik total pendapatan per jam.
     */
public function harian(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));

        // Ambil data penyewaan + relasi meja
        $penyewaans = Penyewaan::with('meja')
            ->whereDate('waktu_mulai', $date)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // 1. Total Pendapatan
        $totalPendapatan = $penyewaans->sum('total_bayar');

        // 2. Distribusi Cash vs QRIS
        $qrisTotal = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashTotal = $penyewaans->where('is_qris', false)->sum('total_bayar');

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

        // 3. Pendapatan per meja
        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // 4. Chart pendapatan per jam
        $dailyRevenue = Penyewaan::select(
            DB::raw('HOUR(waktu_mulai) as hour'),
            DB::raw('SUM(total_bayar) as total')
        )
        ->whereDate('waktu_mulai', $date)
        ->groupBy('hour')
        ->orderBy('hour', 'asc')
        ->get();

        $chartLabels = [];
        $chartData = [];
        for ($i = 0; $i < 24; $i++) {
            $chartLabels[] = sprintf('%02d:00', $i);
            $chartData[$i] = 0;
        }
        foreach ($dailyRevenue as $data) {
            $chartData[$data->hour] = $data->total;
        }
        $chartData = array_values($chartData);

        // 5. Header tabel
        $headers = [
            'ID', 'Nama Penyewa', 'Meja', 'Durasi (Jam)', 'Harga/Jam',
            'Total Layanan', 'Total Bayar', 'Waktu Mulai',
            'Waktu Selesai', 'Status', 'Metode Bayar'
        ];

        return view('laporan.harian', compact(
            'penyewaans',
            'totalPendapatan',
            'paymentMethodDistribution',
            'pendapatanPerMeja',
            'chartLabels',
            'chartData',
            'date',
            'headers'
        ));
    }


    /**
     * Menampilkan laporan bulanan data penyewaan.
     * Termasuk data tabel dan grafik total pendapatan per hari.
     */
    public function bulanan(Request $request)
    {
        // Mendapatkan tahun dan bulan dari request, default bulan/tahun sekarang
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n')); // 'n' untuk bulan tanpa leading zero (1-12)

        // Ambil data penyewaan untuk bulan dan tahun tertentu
        $penyewaans = Penyewaan::whereMonth('waktu_mulai', $month)
                                ->whereYear('waktu_mulai', $year)
                                ->orderBy('waktu_mulai', 'asc')
                                ->get();

        // Agregasi data untuk grafik (total pendapatan per hari)
        $monthlyRevenue = Penyewaan::select(
                                DB::raw('DAY(waktu_mulai) as day'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereMonth('waktu_mulai', $month)
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('day')
                            ->orderBy('day', 'asc')
                            ->get();

        $chartLabels = [];
        $chartData = [];

        // Inisialisasi label dan data untuk semua hari dalam bulan
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabels[] = $i; // Label hari: 1, 2, 3, dst.
            $chartData[$i] = 0; // Default ke 0 jika tidak ada penjualan di hari tersebut
        }

        foreach ($monthlyRevenue as $data) {
            $chartData[$data->day] = $data->total;
        }

        $chartData = array_values($chartData); // Reset keys untuk array JavaScript

        // Untuk dropdown filter bulan/tahun
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 10)); // Mendapatkan nama bulan
        }
        $years = range(date('Y') - 5, date('Y') + 1); // Contoh rentang tahun

        return view('laporan.bulanan', compact('penyewaans', 'chartLabels', 'chartData', 'year', 'month', 'months', 'years'));
    }
}