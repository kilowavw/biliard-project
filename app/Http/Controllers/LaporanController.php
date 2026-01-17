<?php

namespace App\Http\Controllers;

use App\Models\Penyewaan;
use App\Models\ServiceTransaction;
use App\Models\Service; // Pastikan model Service diimport
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class LaporanController extends Controller
{
    const TOP_PERFORMERS_LIMIT = 5;
    const REPORT_START_HOUR = 2; // Rekap harian dimulai dari pukul 02:00 pagi hari berikutnya

    // --- Fungsi Bantuan untuk Menyesuaikan Tanggal Laporan ---
    private function adjustDateForReport(Carbon $originalTime): Carbon
    {
        // Jika waktu transaksi sebelum REPORT_START_HOUR, maka itu milik hari sebelumnya.
        if ($originalTime->hour < self::REPORT_START_HOUR) {
            return $originalTime->copy()->subDay()->startOfDay();
        }
        // Jika tidak, itu milik hari saat ini.
        return $originalTime->copy()->startOfDay();
    }

    // --- Metode Bantuan Umum untuk Mengambil Data Agregat ---
    private function getAggregates($penyewaans, $serviceTransactions, Carbon $reportDate)
    {
        // Filter data yang sesuai dengan tanggal laporan yang telah disesuaikan
        $penyewaansForReport = $penyewaans->filter(function ($item) use ($reportDate) {
            return $this->adjustDateForReport(Carbon::parse($item->waktu_mulai))->toDateString() === $reportDate->toDateString();
        });

        $serviceTransactionsForReport = $serviceTransactions->filter(function ($item) use ($reportDate) {
            return $this->adjustDateForReport(Carbon::parse($item->transaction_time))->toDateString() === $reportDate->toDateString();
        });

        $totalPendapatanPenyewaan = $penyewaansForReport->sum('total_bayar');
        $totalPendapatanServiceTransaction = $serviceTransactionsForReport->sum('total_bayar');
        $totalPendapatanNett = $totalPendapatanPenyewaan + $totalPendapatanServiceTransaction;

        $omzetBilliardMurni = 0;
        $omzetServiceFromPenyewaan = 0;
        foreach ($penyewaansForReport as $p) {
            $omzetBilliardMurni += ($p->total_bayar ?? 0) - ($p->total_service ?? 0);
            $omzetServiceFromPenyewaan += ($p->total_service ?? 0);
        }

        $qrisPenyewaan = $penyewaansForReport->where('is_qris', true)->sum('total_bayar');
        $cashPenyewaan = $penyewaansForReport->where('is_qris', false)->sum('total_bayar');
        $qrisService = $serviceTransactionsForReport->where('payment_method', 'QRIS')->sum('total_bayar');
        $cashService = $serviceTransactionsForReport->where('payment_method', 'Cash')->sum('total_bayar');
        
        $qrisTotal = $qrisPenyewaan + $qrisService;
        $cashTotal = $cashPenyewaan + $cashService;
        
        $paymentMethodDistribution = [];
        if ($cashTotal > 0) { $paymentMethodDistribution[] = ['label' => 'Cash', 'value' => $cashTotal]; }
        if ($qrisTotal > 0) { $paymentMethodDistribution[] = ['label' => 'QRIS', 'value' => $qrisTotal]; }
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 0]; } // Ubah dari 1 ke 0

        // Pastikan $penyewaansForReport memiliki item sebelum grouping
        $pendapatanPerMeja = collect();
        if ($penyewaansForReport->count() > 0) {
            $pendapatanPerMeja = $penyewaansForReport->groupBy('meja_id')->map(function ($items, $mejaId) {
                $namaMeja = $items->first()->meja->nama_meja ?? 'Meja ' . $mejaId;
                return [
                    'meja_id' => $mejaId,
                    'nama_meja' => $namaMeja,
                    'total_pendapatan' => $items->sum('total_bayar'),
                ];
            })->sortByDesc('total_pendapatan')->values();
        }

        // Kinerja Kasir
        $kasirRevenue = collect();
        $penyewaansForReport->each(function ($p) use ($kasirRevenue) {
            if ($p->kasir) {
                $kasirRevenue[$p->kasir->name] = ($kasirRevenue[$p->kasir->name] ?? 0) + $p->total_bayar;
            }
        });
        $serviceTransactionsForReport->each(function ($st) use ($kasirRevenue) {
            if ($st->kasir) {
                $kasirRevenue[$st->kasir->name] = ($kasirRevenue[$st->kasir->name] ?? 0) + $st->total_bayar;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        // Kinerja Pemandu
        $pemanduMejaCount = collect();
        $penyewaansForReport->each(function ($p) use ($pemanduMejaCount) {
            if ($p->pemandu) {
                $pemanduMejaCount[$p->pemandu->name] = ($pemanduMejaCount[$p->pemandu->name] ?? 0) + 1;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        return [
            'totalPendapatanNett' => $totalPendapatanNett,
            'totalPendapatanPenyewaanMurni' => $omzetBilliardMurni,
            'totalPendapatanLayananGabungan' => $omzetServiceFromPenyewaan + $totalPendapatanServiceTransaction,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
            'qrisTotal' => $qrisTotal,
            'cashTotal' => $cashTotal,
        ];
    }

    /**
     * Menampilkan laporan harian data penyewaan dan layanan.
     * Harian dihitung dari pukul 02:00 pagi hari berikutnya. Jadi main jam 01:XX masuk ke hari sebelumnya.
     */
    public function harian(Request $request)
    {
        $date = $request->input('date', date('Y-m-d'));
        $carbonDate = Carbon::parse($date)->startOfDay(); // Tanggal laporan yang dituju

        // Ambil data dari rentang waktu yang mencakup jam 01:00:00 hingga 01:59:59 hari berikutnya
        $startDate = $carbonDate->copy()->addHours(self::REPORT_START_HOUR); // e.g., 2025-11-22 02:00:00
        $endDate = $carbonDate->copy()->addDay()->addHours(self::REPORT_START_HOUR)->subSecond(); // e.g., 2025-11-23 01:59:59

        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                ->where('status', 'dibayar')
                                ->get();

        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereBetween('transaction_time', [$startDate, $endDate])
                                                ->where('payment_status', 'paid')
                                                ->get();

        $aggregates = $this->getAggregates($penyewaans, $serviceTransactions, $carbonDate);

        // Untuk paginasi, tampilkan data yang sesuai dengan rentang waktu di atas
        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                        ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                        ->where('status', 'dibayar')
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereBetween('transaction_time', [$startDate, $endDate])
                                                        ->where('payment_status', 'paid')
                                                        ->orderBy('transaction_time', 'desc')
                                                        ->paginate(10, ['*'], 'service_page');

        return view('laporan.harian', array_merge($aggregates, [
            'date' => $carbonDate->toDateString(),
            'penyewaansPaginated' => $penyewaansPaginated,
            'serviceTransactionsPaginated' => $serviceTransactionsPaginated,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }

    /**
     * Menampilkan laporan bulanan data penyewaan dan layanan.
     * Bulanan dihitung berdasarkan tanggal laporan yang disesuaikan.
     */
    public function bulanan(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));

        // Rentang waktu bulanan dengan penyesuaian jam
        $startDate = Carbon::create($year, $month, 1)->addHours(self::REPORT_START_HOUR);
        $endDate = Carbon::create($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year))->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                ->where('status', 'dibayar')
                                ->get();

        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereBetween('transaction_time', [$startDate, $endDate])
                                                ->where('payment_status', 'paid')
                                                ->get();

        // --- Perhitungan Agregat Bulanan ---
        // Kita buat koleksi semua tanggal dalam bulan ini
        $datesInMonth = collect();
        $currentDate = Carbon::create($year, $month, 1)->startOfDay();
        $lastDate = Carbon::create($year, $month, cal_days_in_month(CAL_GREGORIAN, $month, $year))->startOfDay();

        while ($currentDate <= $lastDate) {
            $datesInMonth->push($currentDate->copy());
            $currentDate->addDay();
        }

        // Kita iterasi setiap tanggal dan hitung agregatnya
        $monthlyAverages = $datesInMonth->map(function ($date) use ($penyewaans, $serviceTransactions) {
            $aggregatesForDate = $this->getAggregates($penyewaans, $serviceTransactions, $date);
            return $aggregatesForDate;
        });

        // Gabungkan agregat harian untuk mendapatkan total bulanan
        $totalPendapatanNett = $monthlyAverages->sum('totalPendapatanNett');
        $totalPendapatanPenyewaanMurni = $monthlyAverages->sum('totalPendapatanPenyewaanMurni');
        $totalPendapatanLayananGabungan = $monthlyAverages->sum('totalPendapatanLayananGabungan');
        $qrisTotal = $monthlyAverages->sum('qrisTotal');
        $cashTotal = $monthlyAverages->sum('cashTotal');

        // Gabungkan distribusi metode pembayaran
        $paymentMethodDistribution = collect();
        $monthlyAverages->each(function ($aggregates) use ($paymentMethodDistribution) {
            foreach ($aggregates['paymentMethodDistribution'] as $item) {
                $existing = $paymentMethodDistribution->firstWhere('label', $item['label']);
                if ($existing) {
                    $existing['value'] += $item['value'];
                } else {
                    $paymentMethodDistribution->push($item);
                }
            }
        });
        $paymentMethodDistribution = $paymentMethodDistribution->values()->toArray();
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 0]; }

        // Gabungkan pendapatan per meja
        $pendapatanPerMeja = collect();
        $monthlyAverages->each(function ($aggregates) use ($pendapatanPerMeja) {
            foreach ($aggregates['pendapatanPerMeja'] as $meja) {
                $existing = $pendapatanPerMeja->firstWhere('meja_id', $meja['meja_id']);
                if ($existing) {
                    $existing['total_pendapatan'] += $meja['total_pendapatan'];
                } else {
                    $pendapatanPerMeja->push($meja);
                }
            }
        });
        $pendapatanPerMeja = $pendapatanPerMeja->sortByDesc('total_pendapatan')->values();

        // Gabungkan top Kasir
        $kasirRevenue = collect();
        $monthlyAverages->each(function ($aggregates) use ($kasirRevenue) {
            foreach ($aggregates['topKasir'] as $name => $total) {
                $kasirRevenue[$name] = ($kasirRevenue[$name] ?? 0) + $total;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        // Gabungkan top Pemandu
        $pemanduMejaCount = collect();
        $monthlyAverages->each(function ($aggregates) use ($pemanduMejaCount) {
            foreach ($aggregates['topPemandu'] as $name => $count) {
                $pemanduMejaCount[$name] = ($pemanduMejaCount[$name] ?? 0) + $count;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        $aggregates = [
            'totalPendapatanNett' => $totalPendapatanNett,
            'totalPendapatanPenyewaanMurni' => $totalPendapatanPenyewaanMurni,
            'totalPendapatanLayananGabungan' => $totalPendapatanLayananGabungan,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
            'qrisTotal' => $qrisTotal,
            'cashTotal' => $cashTotal,
        ];

        // Data chart bulanan: Harus dihitung berdasarkan hari dalam bulan ini
        $chartLabelsDaily = [];
        $chartDataDaily = [];

        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $chartLabelsDaily[] = $i;
            $chartDataDaily[$i] = 0;
        }

        // Iterasi setiap hari dalam bulan, hitung total pendapatan untuk hari itu
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $reportDateForDay = Carbon::create($year, $month, $i)->startOfDay();
            $dailyAggregates = $this->getAggregates($penyewaans, $serviceTransactions, $reportDateForDay);
            $chartDataDaily[$i] = $dailyAggregates['totalPendapatanNett']; // Atau totalPendapatanNett jika ingin total keseluruhan
        }

        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                        ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                        ->where('status', 'dibayar')
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereBetween('transaction_time', [$startDate, $endDate])
                                                        ->where('payment_status', 'paid')
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
            'chartDataDaily' => array_values($chartDataDaily),
            'year' => $year,
            'month' => $month,
            'months' => $months,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }

    /**
     * Menampilkan laporan tahunan data penyewaan dan layanan.
     * Tahunan dihitung berdasarkan tanggal laporan yang disesuaikan.
     */
    public function tahunan(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Rentang waktu tahunan dengan penyesuaian jam
        $startDate = Carbon::create($year, 1, 1)->addHours(self::REPORT_START_HOUR);
        $endDate = Carbon::create($year, 12, 31)->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

        $penyewaans = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                ->where('status', 'dibayar')
                                ->get();

        $serviceTransactions = ServiceTransaction::with('kasir')
                                                ->whereBetween('transaction_time', [$startDate, $endDate])
                                                ->where('payment_status', 'paid')
                                                ->get();

        // --- Perhitungan Agregat Tahunan ---
        // Kita buat koleksi semua bulan dalam tahun ini
        $monthsInYear = collect();
        for ($m = 1; $m <= 12; $m++) {
            $monthsInYear->push($m);
        }

        // Kita iterasi setiap bulan, hitung agregat bulanannya, lalu kumpulkan
        $monthlyAverages = $monthsInYear->map(function ($month) use ($year, $penyewaans, $serviceTransactions) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $startDate = Carbon::create($year, $month, 1)->addHours(self::REPORT_START_HOUR);
            $endDate = Carbon::create($year, $month, $daysInMonth)->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

            // Ambil data hanya untuk bulan ini (filter ulang dari total tahunan)
            $penyewaansThisMonth = $penyewaans->filter(function ($item) use ($startDate, $endDate) {
                $itemTime = Carbon::parse($item->waktu_mulai);
                return $itemTime->between($startDate, $endDate);
            });

            $serviceTransactionsThisMonth = $serviceTransactions->filter(function ($item) use ($startDate, $endDate) {
                $itemTime = Carbon::parse($item->transaction_time);
                return $itemTime->between($startDate, $endDate);
            });

            // Kita buat koleksi semua tanggal dalam bulan ini
            $datesInMonth = collect();
            $currentDate = Carbon::create($year, $month, 1)->startOfDay();
            $lastDate = Carbon::create($year, $month, $daysInMonth)->startOfDay();

            while ($currentDate <= $lastDate) {
                $datesInMonth->push($currentDate->copy());
                $currentDate->addDay();
            }

            // Kita iterasi setiap tanggal dan hitung agregatnya
            $dailyAverages = $datesInMonth->map(function ($date) use ($penyewaansThisMonth, $serviceTransactionsThisMonth) {
                $aggregatesForDate = $this->getAggregates($penyewaansThisMonth, $serviceTransactionsThisMonth, $date);
                return $aggregatesForDate;
            });

            // Gabungkan agregat harian untuk mendapatkan total bulanan
            $totalPendapatanNett = $dailyAverages->sum('totalPendapatanNett');
            $totalPendapatanPenyewaanMurni = $dailyAverages->sum('totalPendapatanPenyewaanMurni');
            $totalPendapatanLayananGabungan = $dailyAverages->sum('totalPendapatanLayananGabungan');
            $qrisTotal = $dailyAverages->sum('qrisTotal');
            $cashTotal = $dailyAverages->sum('cashTotal');

            // Gabungkan distribusi metode pembayaran
            $paymentMethodDistribution = collect();
            $dailyAverages->each(function ($aggregates) use ($paymentMethodDistribution) {
                foreach ($aggregates['paymentMethodDistribution'] as $item) {
                    $existing = $paymentMethodDistribution->firstWhere('label', $item['label']);
                    if ($existing) {
                        $existing['value'] += $item['value'];
                    } else {
                        $paymentMethodDistribution->push($item);
                    }
                }
            });
            $paymentMethodDistribution = $paymentMethodDistribution->values()->toArray();
            if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 0]; }

            // Gabungkan pendapatan per meja
            $pendapatanPerMeja = collect();
            $dailyAverages->each(function ($aggregates) use ($pendapatanPerMeja) {
                foreach ($aggregates['pendapatanPerMeja'] as $meja) {
                    $existing = $pendapatanPerMeja->firstWhere('meja_id', $meja['meja_id']);
                    if ($existing) {
                        $existing['total_pendapatan'] += $meja['total_pendapatan'];
                    } else {
                        $pendapatanPerMeja->push($meja);
                    }
                }
            });
            $pendapatanPerMeja = $pendapatanPerMeja->sortByDesc('total_pendapatan')->values();

            // Gabungkan top Kasir
            $kasirRevenue = collect();
            $dailyAverages->each(function ($aggregates) use ($kasirRevenue) {
                foreach ($aggregates['topKasir'] as $name => $total) {
                    $kasirRevenue[$name] = ($kasirRevenue[$name] ?? 0) + $total;
                }
            });
            $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

            // Gabungkan top Pemandu
            $pemanduMejaCount = collect();
            $dailyAverages->each(function ($aggregates) use ($pemanduMejaCount) {
                foreach ($aggregates['topPemandu'] as $name => $count) {
                    $pemanduMejaCount[$name] = ($pemanduMejaCount[$name] ?? 0) + $count;
                }
            });
            $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

            return [
                'totalPendapatanNett' => $totalPendapatanNett,
                'totalPendapatanPenyewaanMurni' => $totalPendapatanPenyewaanMurni,
                'totalPendapatanLayananGabungan' => $totalPendapatanLayananGabungan,
                'paymentMethodDistribution' => $paymentMethodDistribution,
                'pendapatanPerMeja' => $pendapatanPerMeja,
                'topKasir' => $topKasir,
                'topPemandu' => $topPemandu,
                'qrisTotal' => $qrisTotal,
                'cashTotal' => $cashTotal,
            ];
        });

        // Gabungkan agregat bulanan untuk mendapatkan total tahunan
        $totalPendapatanNett = $monthlyAverages->sum('totalPendapatanNett');
        $totalPendapatanPenyewaanMurni = $monthlyAverages->sum('totalPendapatanPenyewaanMurni');
        $totalPendapatanLayananGabungan = $monthlyAverages->sum('totalPendapatanLayananGabungan');
        $qrisTotal = $monthlyAverages->sum('qrisTotal');
        $cashTotal = $monthlyAverages->sum('cashTotal');

        // Gabungkan distribusi metode pembayaran dari semua bulan
        $paymentMethodDistribution = collect();
        $monthlyAverages->each(function ($aggregates) use ($paymentMethodDistribution) {
            foreach ($aggregates['paymentMethodDistribution'] as $item) {
                $existing = $paymentMethodDistribution->firstWhere('label', $item['label']);
                if ($existing) {
                    $existing['value'] += $item['value'];
                } else {
                    $paymentMethodDistribution->push($item);
                }
            }
        });
        $paymentMethodDistribution = $paymentMethodDistribution->values()->toArray();
        if (empty($paymentMethodDistribution)) { $paymentMethodDistribution[] = ['label' => 'Tidak Ada Data', 'value' => 0]; }

        // Gabungkan pendapatan per meja dari semua bulan
        $pendapatanPerMeja = collect();
        $monthlyAverages->each(function ($aggregates) use ($pendapatanPerMeja) {
            foreach ($aggregates['pendapatanPerMeja'] as $meja) {
                $existing = $pendapatanPerMeja->firstWhere('meja_id', $meja['meja_id']);
                if ($existing) {
                    $existing['total_pendapatan'] += $meja['total_pendapatan'];
                } else {
                    $pendapatanPerMeja->push($meja);
                }
            }
        });
        $pendapatanPerMeja = $pendapatanPerMeja->sortByDesc('total_pendapatan')->values();

        // Gabungkan top Kasir dari semua bulan
        $kasirRevenue = collect();
        $monthlyAverages->each(function ($aggregates) use ($kasirRevenue) {
            foreach ($aggregates['topKasir'] as $name => $total) {
                $kasirRevenue[$name] = ($kasirRevenue[$name] ?? 0) + $total;
            }
        });
        $topKasir = $kasirRevenue->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        // Gabungkan top Pemandu dari semua bulan
        $pemanduMejaCount = collect();
        $monthlyAverages->each(function ($aggregates) use ($pemanduMejaCount) {
            foreach ($aggregates['topPemandu'] as $name => $count) {
                $pemanduMejaCount[$name] = ($pemanduMejaCount[$name] ?? 0) + $count;
            }
        });
        $topPemandu = $pemanduMejaCount->sortDesc()->take(self::TOP_PERFORMERS_LIMIT);

        $aggregates = [
            'totalPendapatanNett' => $totalPendapatanNett,
            'totalPendapatanPenyewaanMurni' => $totalPendapatanPenyewaanMurni,
            'totalPendapatanLayananGabungan' => $totalPendapatanLayananGabungan,
            'paymentMethodDistribution' => $paymentMethodDistribution,
            'pendapatanPerMeja' => $pendapatanPerMeja,
            'topKasir' => $topKasir,
            'topPemandu' => $topPemandu,
            'qrisTotal' => $qrisTotal,
            'cashTotal' => $cashTotal,
        ];

        // Data chart tahunan: Harus dihitung berdasarkan bulan dalam tahun ini
        $chartLabelsMonthly = [];
        $chartDataMonthly = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthName = date('F', mktime(0, 0, 0, $i, 1));
            $chartLabelsMonthly[] = $monthName;
            $chartDataMonthly[$i] = 0;
        }

        // Iterasi setiap bulan dalam tahun, hitung total pendapatan untuk bulan itu
        for ($m = 1; $m <= 12; $m++) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $m, $year);
            $startDate = Carbon::create($year, $m, 1)->addHours(self::REPORT_START_HOUR);
            $endDate = Carbon::create($year, $m, $daysInMonth)->addHours(self::REPORT_START_HOUR)->addDay()->subSecond();

            // Ambil data hanya untuk bulan ini (filter ulang dari total tahunan)
            $penyewaansThisMonth = $penyewaans->filter(function ($item) use ($startDate, $endDate) {
                $itemTime = Carbon::parse($item->waktu_mulai);
                return $itemTime->between($startDate, $endDate);
            });

            $serviceTransactionsThisMonth = $serviceTransactions->filter(function ($item) use ($startDate, $endDate) {
                $itemTime = Carbon::parse($item->transaction_time);
                return $itemTime->between($startDate, $endDate);
            });

            // Hitung agregat untuk bulan ini
            $daysInCurrentMonth = collect();
            $currentDate = Carbon::create($year, $m, 1)->startOfDay();
            $lastDate = Carbon::create($year, $m, $daysInMonth)->startOfDay();

            while ($currentDate <= $lastDate) {
                $daysInCurrentMonth->push($currentDate->copy());
                $currentDate->addDay();
            }

            $monthlyAveragesForChart = $daysInCurrentMonth->map(function ($date) use ($penyewaansThisMonth, $serviceTransactionsThisMonth) {
                $aggregatesForDate = $this->getAggregates($penyewaansThisMonth, $serviceTransactionsThisMonth, $date);
                return $aggregatesForDate;
            });

            $chartDataMonthly[$m] = $monthlyAveragesForChart->sum('totalPendapatanNett'); // Atau totalPendapatanNett jika ingin total keseluruhan
        }

        $penyewaansPaginated = Penyewaan::with(['meja', 'kasir', 'pemandu', 'paket'])
                                        ->whereBetween('waktu_mulai', [$startDate, $endDate])
                                        ->where('status', 'dibayar')
                                        ->orderBy('waktu_mulai', 'desc')
                                        ->paginate(10, ['*'], 'penyewaan_page');

        $serviceTransactionsPaginated = ServiceTransaction::with('kasir')
                                                        ->whereBetween('transaction_time', [$startDate, $endDate])
                                                        ->where('payment_status', 'paid')
                                                        ->orderBy('transaction_time', 'desc')
                                                        ->paginate(10, ['*'], 'service_page');

        $years = range(date('Y') - 5, date('Y') + 1);

        return view('laporan.tahunan', array_merge($aggregates, [
            'penyewaansPaginated' => $penyewaansPaginated,
            'serviceTransactionsPaginated' => $serviceTransactionsPaginated,
            'chartLabelsMonthly' => $chartLabelsMonthly,
            'chartDataMonthly' => array_values($chartDataMonthly),
            'year' => $year,
            'years' => $years,
            'TOP_PERFORMERS_LIMIT' => self::TOP_PERFORMERS_LIMIT,
        ]));
    }

    /**
     * Menampilkan laporan stok.
     */
    public function stok(Request $request)
    {
        $services = Service::orderBy('kategori')->orderBy('nama')->get();

        return view('laporan.stok', [
            'services' => $services,
        ]);
    }
}