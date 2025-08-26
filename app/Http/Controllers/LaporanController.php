<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use App\Models\ServiceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator; // Import untuk manual pagination

class LaporanController extends Controller
{
    // Konstanta untuk menentukan berapa banyak kasir/pemandu teratas yang ditampilkan
    const TOP_PERFORMERS_LIMIT = 5;

    /**
     * Metode Bantuan untuk Mengambil Data Agregat Harian.
     * Menggabungkan data dari Penyewaan dan ServiceTransaction.
     */
    private function getDailyAggregates($date)
    {
        // Data Penyewaan
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereDate('waktu_mulai', $date)
                                ->get();

        // Data ServiceTransaction
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereDate('transaction_time', $date)
                                                ->get();

        // Gabungkan pendapatan untuk total nett
        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        // Distribusi Pembayaran (Cash vs QRIS)
        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');

        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');

        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;

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

        // Pendapatan Per Meja (hanya dari Penyewaan)
        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Kinerja Kasir
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

        // Kinerja Pemandu
        // Kinerja Pemandu (DIUBAH berdasarkan COUNT meja)
        $pemanduMejaCount = collect();
        $penyewaans->each(function ($p) use ($pemanduMejaCount) {
            if ($p->pemandu) {
                // Setiap kali ada transaksi Penyewaan yang terkait dengan pemandu,
                // kita tambahkan 1 ke hitungannya. Ini merepresentasikan 1 meja.
                $pemanduMejaCount[$p->pemandu->name] = ($pemanduMejaCount[$p->pemandu->name] ?? 0) + 1;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        return [
            'penyewaans' => $penyewaans, // Semua data penyewaan (non-paginated)
            'serviceTransactions' => $serviceTransactions, // Semua data service (non-paginated)
            'totalPendapatanNett' => $totalPendapatanNett,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
        ];
    }

    /**
     * Metode Bantuan untuk Mengambil Data Agregat Bulanan.
     * Menggabungkan data dari Penyewaan dan ServiceTransaction.
     */
    private function getMonthlyAggregates($year, $month)
    {
        // Data Penyewaan
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereMonth('waktu_mulai', $month)
                                ->whereYear('waktu_mulai', $year)
                                ->get();

        // Data ServiceTransaction
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereMonth('transaction_time', $month)
                                                ->whereYear('transaction_time', $year)
                                                ->get();

        // Gabungkan pendapatan untuk total nett
        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        // Distribusi Pembayaran (Cash vs QRIS)
        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');

        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');

        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;

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

        // Pendapatan Per Meja (hanya dari Penyewaan)
        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Kinerja Kasir
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

        // Kinerja Pemandu
        $pemanduRevenue = collect();
        $penyewaans->each(function ($p) use ($pemanduRevenue) {
            if ($p->pemandu) {
                $pemanduRevenue[$p->pemandu->name] = ($pemanduRevenue[$p->pemandu->name] ?? 0) + $p->total_bayar;
            }
        });
        $topPemandu = $pemanduRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

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
     * Metode Bantuan untuk Mengambil Data Agregat Tahunan.
     * Menggabungkan data dari Penyewaan dan ServiceTransaction.
     */
    private function getYearlyAggregates($year)
    {
        // Data Penyewaan
        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu'])
                                ->whereYear('waktu_mulai', $year)
                                ->get();

        // Data ServiceTransaction
        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereYear('transaction_time', $year)
                                                ->get();

        // Gabungkan pendapatan untuk total nett
        $totalPendapatanPenyewaan = $penyewaans->sum('total_bayar');
        $totalPendapatanService = $serviceTransactions->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanService;

        // Distribusi Pembayaran (Cash vs QRIS)
        $qrisPenyewaan = $penyewaans->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaans->where('is_qris', false)->sum('total_bayar');

        $qrisService = $serviceTransactions->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactions->where('payment_method', 'Cash')->sum('total_bayar');

        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;

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

        // Pendapatan Per Meja (hanya dari Penyewaan)
        $pendapatanPerMeja = $penyewaans->groupBy('meja_id')->map(function ($items, $mejaId) {
            $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
            return [
                'meja_id' => $mejaId,
                'nama_meja' => $namaMeja,
                'total_pendapatan' => $items->sum('total_bayar'),
            ];
        })->sortByDesc('total_pendapatan')->values();

        // Kinerja Kasir
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

        // Kinerja Pemandu
        $pemanduRevenue = collect();
        $penyewaans->each(function ($p) use ($pemanduRevenue) {
            if ($p->pemandu) {
                $pemanduRevenue[$p->pemandu->name] = ($pemanduRevenue[$p->pemandu->name] ?? 0) + $p->total_bayar;
            }
        });
        $topPemandu = $pemanduRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

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
     * Menampilkan laporan harian data penyewaan dan layanan dengan pagination untuk detail transaksi.
     */
    public function harian(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $aggregates = $this->getDailyAggregates($date); // Panggil metode bantuan

        // Gabungkan kedua koleksi untuk pagination pada detail transaksi
        $allTransactionsQuery = collect()
            ->concat($aggregates['penyewaans']->map(function($item) {
                $item->type = 'Penyewaan';
                return $item;
            }))
            ->concat($aggregates['serviceTransactions']->map(function($item) {
                $item->type = 'Service';
                return $item;
            }))
            ->sortByDesc(function ($item) {
                return $item->transaction_time ?? $item->waktu_mulai;
            });

        // Manual Pagination karena menggabungkan 2 koleksi
        $page = $request->get('page', 1);
        $perPage = 10; // Items per page
        $offset = ($page * $perPage) - $perPage;
        $paginatedTransactions = new LengthAwarePaginator(
            $allTransactionsQuery->slice($offset, $perPage),
            $allTransactionsQuery->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('laporan.harian', array_merge($aggregates, [
            'date' => $date,
            'penyewaansPaginated' => $paginatedTransactions, // Ini adalah semua transaksi yang digabungkan dan di-paginate
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT, // Kirim konstanta ke view
        ]));
    }

    /**
     * Menampilkan laporan bulanan data penyewaan dan layanan dengan pagination untuk detail transaksi.
     */
    public function bulanan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        $aggregates = $this->getMonthlyAggregates($year, $month); // Panggil metode bantuan

        // --- Data untuk Agregasi Grafik (Total Pendapatan Per Hari) ---
        // Pendapatan dari Penyewaan per hari
        $monthlyRevenuePenyewaan = Penyewaan::select(
                                DB::raw('DAY(waktu_mulai) as day'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereMonth('waktu_mulai', $month)
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('day')
                            ->orderBy('day', 'asc')
                            ->get();

        // Pendapatan dari ServiceTransaction per hari
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
            $chartDataDaily[$i] = 0; // Inisialisasi dengan 0
        }

        // Akumulasi pendapatan dari Penyewaan dan ServiceTransaction
        foreach ($monthlyRevenuePenyewaan as $data) {
            $chartDataDaily[$data->day] += $data->total;
        }
        foreach ($monthlyRevenueService as $data) {
            $chartDataDaily[$data->day] += $data->total;
        }
        $chartDataDaily = array_values($chartDataDaily); // Reset keys untuk array JavaScript


        // Gabungkan kedua koleksi untuk pagination pada detail transaksi
        $allTransactionsQuery = collect()
            ->concat($aggregates['penyewaans']->map(function($item) {
                $item->type = 'Penyewaan';
                return $item;
            }))
            ->concat($aggregates['serviceTransactions']->map(function($item) {
                $item->type = 'Service';
                return $item;
            }))
            ->sortByDesc(function ($item) {
                return $item->transaction_time ?? $item->waktu_mulai;
            });

        // Manual Pagination karena menggabungkan 2 koleksi
        $page = $request->get('page', 1);
        $perPage = 10; // Items per page
        $offset = ($page * $perPage) - $perPage;
        $paginatedTransactions = new LengthAwarePaginator(
            $allTransactionsQuery->slice($offset, $perPage),
            $allTransactionsQuery->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Untuk dropdown filter bulan/tahun
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 10));
        }
        $years = range(date('Y') - 5, date('Y') + 1);

        return view('laporan.bulanan', array_merge($aggregates, [
            'penyewaansPaginated' => $paginatedTransactions, // Ini adalah semua transaksi yang digabungkan dan di-paginate
            'chartLabelsDaily' => $chartLabelsDaily,
            'chartDataDaily' => $chartDataDaily,
            'year' => $year,
            'month' => $month,
            'months' => $months,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT, // Kirim konstanta ke view
        ]));
    }

    /**
     * Menampilkan laporan tahunan yang diagregasi per bulan dengan pagination untuk detail transaksi.
     */
    public function tahunan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $aggregates = $this->getYearlyAggregates($year); // Panggil metode bantuan

        // --- Data untuk Grafik "Total Pendapatan Per Bulan" (ApexCharts Bar Chart) ---
        // Pendapatan dari Penyewaan per bulan
        $monthlyRevenuePenyewaan = Penyewaan::select(
                                DB::raw('MONTH(waktu_mulai) as month_num'),
                                DB::raw('SUM(total_bayar) as total')
                            )
                            ->whereYear('waktu_mulai', $year)
                            ->groupBy('month_num')
                            ->orderBy('month_num', 'asc')
                            ->get();

        // Pendapatan dari ServiceTransaction per bulan
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
            $chartDataMonthly[$i] = 0; // Inisialisasi dengan 0
        }

        // Akumulasi pendapatan dari Penyewaan dan ServiceTransaction
        foreach ($monthlyRevenuePenyewaan as $data) {
            $chartDataMonthly[$data->month_num] += $data->total;
        }
        foreach ($monthlyRevenueService as $data) {
            $chartDataMonthly[$data->month_num] += $data->total;
        }
        $chartDataMonthly = array_values($chartDataMonthly); // Reset keys untuk array JavaScript


        // Gabungkan kedua koleksi untuk pagination pada detail transaksi
        $allTransactionsQuery = collect()
            ->concat($aggregates['penyewaans']->map(function($item) {
                $item->type = 'Penyewaan';
                return $item;
            }))
            ->concat($aggregates['serviceTransactions']->map(function($item) {
                $item->type = 'Service';
                return $item;
            }))
            ->sortByDesc(function ($item) {
                return $item->transaction_time ?? $item->waktu_mulai;
            });

        // Manual Pagination karena menggabungkan 2 koleksi
        $page = $request->get('page', 1);
        $perPage = 10; // Items per page
        $offset = ($page * $perPage) - $perPage;
        $paginatedTransactions = new LengthAwarePaginator(
            $allTransactionsQuery->slice($offset, $perPage),
            $allTransactionsQuery->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Untuk dropdown filter tahun
        $years = range(date('Y') - 5, date('Y') + 1);

        return view('laporan.tahunan', array_merge($aggregates, [
            'penyewaansPaginated' => $paginatedTransactions, // Ini adalah semua transaksi yang digabungkan dan di-paginate
            'chartLabelsMonthly' => $chartLabelsMonthly,
            'chartDataMonthly' => $chartDataMonthly,
            'year' => $year,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT, // Kirim konstanta ke view
        ]));
    }
}