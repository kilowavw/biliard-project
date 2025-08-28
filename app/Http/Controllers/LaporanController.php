<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use App\Models\ServiceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class LaporanController extends Controller
{
    const TOP_PERFORMERS_LIMIT = 5;

    // --- Metode Bantuan untuk Mengambil Data Agregat Harian ---
    private function getDailyAggregates($date)
    {
        // Pastikan eager loading dilakukan di sini
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereDate('waktu_mulai', $date)
                                ->get();
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereDate('transaction_time', $date)
                                                ->get();

        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');
        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');
        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;
        $paymentMethodDistribution = [];
        if ($cashTotal > 0) { $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal]; }
        if ($qrisTotal > 0) { $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal]; }
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1]; }

        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            // Perbaikan: Pastikan $items->first() tidak null dan meja ada
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [ 'meja_id' => $mejaId, 'nama_meja' => $namaMeja, 'total_pendapatan' => $items->sum('total_bayar'), ];
        })->sortByDesc('total_pendapatan')->values();


        // Kinerja Kasir
        $kasirRevenue = collect();
        $penyewaans->each(function ($p) use ($kasirRevenue) {
            if ($p->kasir) { // Perbaikan: Cek jika relasi kasir ada
                $kasirRevenue[$p->kasir->name] = ($kasirRevenue[$p->kasir->name] ?? 0) + $p->total_bayar;
            }
        });
        $serviceTransactions->each(function ($st) use ($kasirRevenue) {
            if ($st->kasir) { // Perbaikan: Cek jika relasi kasir ada
                $kasirRevenue[$st->kasir->name] = ($kasirRevenue[$st->kasir->name] ?? 0) + $st->total_bayar;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        // Kinerja Pemandu
        $pemanduMejaCount = collect();
        $penyewaans->each(function ($p) use ($pemanduMejaCount) {
            if ($p->pemandu) { // Perbaikan: Cek jika relasi pemandu ada
                $pemanduMejaCount[$p->pemandu->name] = ($pemanduMejaCount[$p->pemandu->name] ?? 0) + 1;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        return [
            'penyewaans' => $penyewaans,
            'serviceTransactions' => $serviceTransactions,
            'totalPendapatanNett' => $totalPendapatanNett,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
        ];
    }

    // --- Metode Bantuan untuk Mengambil Data Agregat Bulanan ---
    private function getMonthlyAggregates($year, $month)
    {
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereMonth('waktu_mulai', $month)
                                ->whereYear('waktu_mulai', $year)
                                ->get();
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereMonth('transaction_time', $month)
                                                ->whereYear('transaction_time', $year)
                                                ->get();

        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');
        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');
        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;
        $paymentMethodDistribution = [];
        if ($cashTotal > 0) { $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal]; }
        if ($qrisTotal > 0) { $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal]; }
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1]; }

        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [ 'meja_id' => $mejaId, 'nama_meja' => $namaMeja, 'total_pendapatan' => $items->sum('total_bayar'), ];
        })->sortByDesc('total_pendapatan')->values();

        $kasirRevenue = collect();
        $penyewaans->each(function ($p) use ($kasirRevenue) {
            if ($p->kasir) {
                $kasirRevenue[$p->kasir->name] = ($kasirRevenue[$p->kasir->name] ?? 0) + $p->total_bayar;
            }
        });
        $serviceTransactions->each(function ($st) use ($kasirRevenue) {
            if ($st->kasir) {
                $kasirRevenue[$st->kasir->name] = ($kasirRevenue[$st->kasir->name] ?? 0) + $st->total_bayar;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        $pemanduMejaCount = collect();
        $penyewaans->each(function ($p) use ($pemanduMejaCount) {
            if ($p->pemandu) {
                $pemanduMejaCount[$p->pemandu->name] = ($pemanduMejaCount[$p->pemandu->name] ?? 0) + 1;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        return [
            'penyewaans' => $penyewaans,
            'serviceTransactions' => $serviceTransactions,
            'totalPendapatanNett' => $totalPendapatanNett,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
        ];
    }

    // --- Metode Bantuan untuk Mengambil Data Agregat Tahunan ---
    private function getYearlyAggregates($year)
    {
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereYear('waktu_mulai', $year)
                                ->get();
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereYear('transaction_time', $year)
                                                ->get();

        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');
        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');
        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;
        $paymentMethodDistribution = [];
        if ($cashTotal > 0) { $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal]; }
        if ($qrisTotal > 0) { $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal]; }
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 1]; }

        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [ 'meja_id' => $mejaId, 'nama_meja' => $namaMeja, 'total_pendapatan' => $items->sum('total_bayar'), ];
        })->sortByDesc('total_pendapatan')->values();

        $kasirRevenue = collect();
        $penyewaans->each(function ($p) use ($kasirRevenue) {
            if ($p->kasir) {
                $kasirRevenue[$p->kasir->name] = ($kasirRevenue[$p->kasir->name] ?? 0) + $p->total_bayar;
            }
        });
        $serviceTransactions->each(function ($st) use ($kasirRevenue) {
            if ($st->kasir) {
                $kasirRevenue[$st->kasir->name] = ($kasirRevenue[$st->kasir->name] ?? 0) + $st->total_bayar;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        $pemanduMejaCount = collect();
        $penyewaans->each(function ($p) use ($pemanduMejaCount) {
            if ($p->pemandu) {
                $pemanduMejaCount[$p->pemandu->name] = ($pemanduMejaCount[$p->pemandu->name] ?? 0) + 1;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        return [
            'penyewaans' => $penyewaans,
            'serviceTransactions' => $serviceTransactions,
            'totalPendapatanNett' => $totalPendapatanNett,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
        ];
    }


    /**
     * Menampilkan laporan harian data penyewaan dan layanan.
     */
    public function harian(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $aggregates = $this->getDailyAggregates($date);

        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                        ->whereDate('waktu_mulai', $date)
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereDate('transaction_time', $date)
                                                        ->orderBy('transaction_time', 'desc')
                                                        ->paginate(10, ['*'], 'service_page');


        return view('laporan.harian', array_merge($aggregates, [
            'date' => $date,
            'penyewaansPaginated' => $penyewaansPaginated,
            'serviceTransactionsPaginated' => $serviceTransactionsPaginated,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }

    /**
     * Menampilkan laporan bulanan data penyewaan dan layanan.
     */
    public function bulanan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        $aggregates = $this->getMonthlyAggregates($year, $month);

        $monthlyRevenuePenyewaan = Penyewaan::select(
                                DB::raw('DAY(waktu_mulai) as day'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereMonth('waktu_mulai', $month)
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('day')
                            ->orderBy('day', 'asc')
                            ->get();

        $monthlyRevenueService = ServiceTransaction::select(
                                DB::raw('DAY(transaction_time) as day'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereMonth('transaction_time', $month)
                            ->whereYear('transaction_time', $year)
                            ->groupBy('day')
                            ->orderBy('day', 'asc')
                            ->get();

        $chartLabelsDaily = [];
        $chartDataDaily = [];

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabelsDaily[] = $i;
            $chartDataDaily[$i] = 0;
        }

        foreach ($monthlyRevenuePenyewaan as $data) {
            $chartDataDaily[$data->day] += $data->total;
        }
        foreach ($monthlyRevenueService as $data) {
            $chartDataDaily[$data->day] += $data->total;
        }
        $chartDataDaily = array_values($chartDataDaily);


        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                        ->whereMonth('waktu_mulai', $month)
                                        ->whereYear('waktu_mulai', $year)
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereMonth('transaction_time', $month)
                                                        ->whereYear('transaction_time', $year)
                                                        ->orderBy('transaction_time', 'desc')
                                                        ->paginate(10, ['*'], 'service_page');

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 10));
        }
        $years = range(date('Y') - 5, date('Y') + 1);

        return view('laporan.bulanan', array_merge($aggregates, [
            'penyewaansPaginated' => $penyewaansPaginated,
            'serviceTransactionsPaginated' => $serviceTransactionsPaginated,
            'chartLabelsDaily' => $chartLabelsDaily,
            'chartDataDaily' => $chartDataDaily,
            'year' => $year,
            'month' => $month,
            'months' => $months,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }

    /**
     * Menampilkan laporan tahunan data penyewaan dan layanan.
     */
    public function tahunan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $aggregates = $this->getYearlyAggregates($year);

        $monthlyRevenuePenyewaan = Penyewaan::select(
                                DB::raw('MONTH(waktu_mulai) as month_num'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('month_num')
                            ->orderBy('month_num', 'asc')
                            ->get();

        $monthlyRevenueService = ServiceTransaction::select(
                                DB::raw('MONTH(transaction_time) as month_num'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereYear('transaction_time', $year)
                            ->groupBy('month_num')
                            ->orderBy('month_num', 'asc')
                            ->get();

        $chartLabelsMonthly = [];
        $chartDataMonthly = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $chartLabelsMonthly[] = $monthName;
            $chartDataMonthly[$i] = 0;
        }

        foreach ($monthlyRevenuePenyewaan as $data) {
            $chartDataMonthly[$data->month_num] += $data->total;
        }
        foreach ($monthlyRevenueService as $data) {
            $chartDataMonthly[$data->month_num] += $data->total;
        }
        $chartDataMonthly = array_values($chartDataMonthly);


        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                        ->whereYear('waktu_mulai', $year)
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereYear('transaction_time', $year)
                                                        ->orderBy('transaction_time', 'desc')
                                                        ->paginate(10, ['*'], 'service_page');

        $years = range(date('Y') - 5, date('Y') + 1);

        return view('laporan.tahunan', array_merge($aggregates, [
            'penyewaansPaginated' => $penyewaansPaginated,
            'serviceTransactionsPaginated' => $serviceTransactionsPaginated,
            'chartLabelsMonthly' => $chartLabelsMonthly,
            'chartDataMonthly' => $chartDataMonthly,
            'year' => $year,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }
}