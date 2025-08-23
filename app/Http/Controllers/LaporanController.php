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
        // Mendapatkan tanggal dari request, default hari ini
        $date = $request->input('date', date('Y-m-d')); // Y-m-d format

        // Ambil data penyewaan untuk tanggal tertentu
        $penyewaans = Penyewaan::whereDate('waktu_mulai', $date)
                                ->orderBy('waktu_mulai', 'asc')
                                ->get();

        // Agregasi data untuk grafik (total pendapatan per jam)
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

        // Inisialisasi label dan data untuk semua jam dalam sehari (0-23)
        for ($i = 0; $i < 24; $i++) {
            $chartLabels[] = sprintf('%02d:00', $i); // Format jam: 00:00, 01:00, dst.
            $chartData[$i] = 0; // Default ke 0 jika tidak ada penjualan di jam tersebut
        }

        foreach ($dailyRevenue as $data) {
            $chartData[$data->hour] = $data->total;
        }

        $chartData = array_values($chartData); // Reset keys untuk array JavaScript

        return view('laporan.harian', compact('penyewaans', 'chartLabels', 'chartData', 'date'));
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