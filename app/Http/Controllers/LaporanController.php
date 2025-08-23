<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Service; // Pastikan Service model di-import

class LaporanController extends Controller
{
    public function dailyReport(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $startDate = Carbon::parse($date)->startOfDay();
        $endDate = Carbon::parse($date)->endOfDay();

        $penyewaans = Penyewaan::with(['meja', 'kasir', 'paket'])
            ->whereBetween('waktu_mulai', [$startDate, $endDate])
            ->where('status', 'dibayar')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Data untuk Chart
        $totalPendapatan = $penyewaans->sum('total_bayar');

        $pendapatanPerJam = Penyewaan::select(
                DB::raw("DATE_FORMAT(waktu_mulai, '%H:00') as hour"),
                DB::raw("SUM(total_bayar) as total")
            )
            ->whereBetween('waktu_mulai', [$startDate, $endDate])
            ->where('status', 'dibayar')
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        // Hitung pendapatan per kategori service secara manual dari service_detail
        $serviceCategoriesData = [];
        foreach ($penyewaans as $penyewaan) {
            // Pastikan service_detail adalah array dan tidak kosong sebelum diulang
            if (is_array($penyewaan->service_detail) && !empty($penyewaan->service_detail)) {
                foreach ($penyewaan->service_detail as $serviceItem) {
                    if (isset($serviceItem['id']) && isset($serviceItem['subtotal'])) {
                        $service = Service::find($serviceItem['id']); // Menggunakan alias Service
                        $category = $service ? $service->kategori : 'Lain-lain';

                        if (!isset($serviceCategoriesData[$category])) {
                            $serviceCategoriesData[$category] = 0;
                        }
                        $serviceCategoriesData[$category] += $serviceItem['subtotal'];
                    }
                }
            }
        }

        // Total penyewaan per jenis (durasi tetap, sepuasnya, paket)
        $penyewaanJenis = $penyewaans->groupBy(function($item) {
            if ($item->paket_id) {
                return 'paket';
            } elseif ($item->durasi_jam > 0 && !is_null($item->waktu_selesai)) {
                return 'durasi_tetap';
            } else {
                return 'sepuasnya';
            }
        })->map->count();


        return view('admin.reports.daily', compact(
            'penyewaans', 'date', 'totalPendapatan',
            'pendapatanPerJam', 'serviceCategoriesData', 'penyewaanJenis'
        ));
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->input('month', Carbon::today()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $penyewaans = Penyewaan::with(['meja', 'kasir', 'paket'])
            ->whereBetween('waktu_mulai', [$startDate, $endDate])
            ->where('status', 'dibayar')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $totalPendapatan = $penyewaans->sum('total_bayar');

        // Pendapatan harian untuk chart bulanan
        $pendapatanHarian = Penyewaan::select(
                DB::raw("DATE_FORMAT(waktu_mulai, '%Y-%m-%d') as day"),
                DB::raw("SUM(total_bayar) as total")
            )
            ->whereBetween('waktu_mulai', [$startDate, $endDate])
            ->where('status', 'dibayar')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get();

        // Menghitung pendapatan per kategori service secara manual
        $serviceCategoriesData = [];
        foreach ($penyewaans as $penyewaan) {
            // Pastikan service_detail adalah array dan tidak kosong sebelum diulang
            if (is_array($penyewaan->service_detail) && !empty($penyewaan->service_detail)) {
                foreach ($penyewaan->service_detail as $serviceItem) {
                    if (isset($serviceItem['id']) && isset($serviceItem['subtotal'])) {
                        $service = Service::find($serviceItem['id']); // Menggunakan alias Service
                        $category = $service ? $service->kategori : 'Lain-lain';

                        if (!isset($serviceCategoriesData[$category])) {
                            $serviceCategoriesData[$category] = 0;
                        }
                        $serviceCategoriesData[$category] += $serviceItem['subtotal'];
                    }
                }
            }
        }

        $penyewaanJenis = $penyewaans->groupBy(function($item) {
            if ($item->paket_id) {
                return 'paket';
            } elseif ($item->durasi_jam > 0 && !is_null($item->waktu_selesai)) {
                return 'durasi_tetap';
            } else {
                return 'sepuasnya';
            }
        })->map->count();

        return view('admin.reports.monthly', compact(
            'penyewaans', 'month', 'totalPendapatan',
            'pendapatanHarian', 'serviceCategoriesData', 'penyewaanJenis'
        ));
    }
}