@extends('default')

@section('title', 'Laporan Bulanan')

@section('content')

<?php
// Variabel PHP sudah disediakan dari controller:
// $penyewaansPaginated, $totalPendapatan, $paymentMethodDistribution, $pendapatanPerMeja
// $chartLabelsDaily, $chartDataDaily, $year, $month, $months, $years

$headers = [
    'ID', 'Nama Penyewa', 'Meja', 'Durasi (Jam)', 'Harga/Jam',
    'Total Layanan', 'Total Bayar', 'Waktu Mulai', 'Waktu Selesai', 'Status', 'Metode Bayar'
];
?>

<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Laporan Bulanan Penyewaan</h1>

    <form id="month-year-form" action="{{ route('laporan.bulanan') }}" method="GET" class="mb-6 flex flex-col md:flex-row items-center gap-4">
       {{-- Link untuk laporan harian --}}
        <a href="{{ route('laporan.harian') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.harian') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-day mr-3"></i> Laporan Harian
                </a>

        {{-- Link untuk laporan tahunan --}}
        <a href="{{ route('laporan.bulanan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.bulanan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Bulanan
                </a>
        {{-- Link untuk laporan tahunan --}}
        <a href="{{ route('laporan.tahunan') }}" class="flex items-center px-4 py-2 rounded
                    {{ request()->routeIs('laporan.tahunan') ? 'text-white bg-[#282828]' : 'hover:bg-[#282828]' }}">
                    <i class="fa-solid fa-calendar-days mr-3"></i> Laporan Tahunan
                </a>

        <label for="report-month" class="block text-sm font-medium text-gray-300">Pilih Bulan:</label>
        <select id="report-month" name="month"
                class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2.5">
            @foreach($months as $key => $name)
                <option value="{{ $key }}" {{ $key == $month ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>

        <label for="report-year" class="block text-sm font-medium text-gray-300">Pilih Tahun:</label>
        <select id="report-year" name="year"
                class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-auto p-2.5">
            @foreach($years as $y)
                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button type="submit" class="hidden">Submit</button>
    </form>

    {{-- Total Pendapatan Bulanan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pendapatan (Nett) {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</p>
                <p id="total-pendapatan" class="text-3xl font-bold text-white mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-blue-900 rounded-full">
                <i class="fa-solid fa-rupiah-sign text-blue-300 text-2xl"></i>
            </div>
        </div>
    </div>

    {{-- Grafik Tren Pendapatan Harian (ApexCharts) --}}
    <div class="w-full bg-[#1e1e1e] rounded-lg shadow dark:bg-gray-800 p-4 md:p-6 mb-8 border border-gray-700">
        <div class="flex justify-between mb-3">
            <div class="flex justify-center items-center">
                <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white pe-1">Tren Pendapatan {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }} (Per Hari)</h5>
            </div>
        </div>
        <div id="monthly-daily-revenue-chart"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Grafik Distribusi Pendapatan (Chart.js Doughnut) --}}
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Distribusi Pendapatan {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
            <div class="chart-container w-full h-80 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Data distribusi Cash & QRIS diambil dari kolom 'is_qris'.
            </p>
        </div>
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Meja {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
    <div id="pendapatan-meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($pendapatanPerMeja as $meja)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $meja['nama_meja'] }}</h3>
                <p class="text-xl font-bold text-green-400 mt-2">Rp {{ number_format($meja['total_pendapatan'], 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada pendapatan tercatat untuk meja pada bulan ini.</p>
        @endforelse
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Detail Penyewaan {{ date('F Y', mktime(0, 0, 0, $month, 10, $year)) }}</h2>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mb-4">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-gray-300">
                <tr>
                    @foreach($headers as $header)
                           <th scope="col" class="px-6 py-3 text-white">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($penyewaansPaginated as $penyewaan)
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4">{{ $penyewaan->id }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->nama_penyewa }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->meja->nama_meja ?? $penyewaan->meja_id }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->durasi_jam }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->harga_per_jam, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->total_service, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->total_bayar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->waktu_mulai->format('d/m/Y H:i:s') }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->waktu_selesai ? $penyewaan->waktu_selesai->format('d/m/Y H:i:s') : '-' }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->status }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->is_qris ? 'QRIS' : 'Cash' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada data penyewaan untuk bulan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Tautan Pagination --}}
    <div class="mt-4">
        {{ $penyewaansPaginated->appends(request()->query())->links() }}
    </div>

</div>
@endsection

@section('script')
{{-- Chart.js untuk Doughnut Chart --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{{-- ApexCharts untuk Bar Chart --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

<script>
    const fmtRp = (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    };

    let paymentMethodChartInstance = null;
    const paymentMethodDistributionData = @json($paymentMethodDistribution);

    const renderPaymentMethodChart = (distributionData) => {
        const ctx = document.getElementById('paymentMethodChart');
        if (!ctx) return;

        const labels = distributionData.map(item => item.label);
        const values = distributionData.map(item => item.value);

        if (paymentMethodChartInstance) paymentMethodChartInstance.destroy();

        paymentMethodChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: ['#10B981', '#3B82F6', '#6B7280'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#D1D5DB' }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (context.parsed !== null) {
                                    if (distributionData.some(item => item.value > 0)) {
                                        label += ': ' + fmtRp(context.parsed);
                                    } else {
                                        label = 'Tidak Ada Data';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    };

    const chartLabelsDaily = @json($chartLabelsDaily);
    const chartDataDaily = @json($chartDataDaily);

    const dailyRevenueChartOptions = {
        series: [{
            name: "Pendapatan",
            data: chartDataDaily,
        }],
        chart: {
            height: 350,
            type: "bar",
            fontFamily: "Inter, sans-serif",
            toolbar: {
                show: false,
            },
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "70%",
                borderRadiusApplication: "end",
                borderRadius: 8,
            },
        },
        tooltip: {
            theme: 'dark',
            shared: true,
            intersect: false,
            style: {
                fontFamily: "Inter, sans-serif",
            },
            y: {
                formatter: function (val) {
                    return fmtRp(val);
                }
            }
        },
        states: {
                hover: {
                    filter: {
                        type: "darken",
                        value: 1,
                    },
                },
            },
        stroke: {
            show: true,
            width: 0,
            colors: ["transparent"],
        },
        grid: {
            show: false,
            strokeDashArray: 4,
            padding: {
                left: 2,
                right: 2,
                top: -14
            },
        },
        dataLabels: {
            enabled: false,
        },
        legend: {
            show: false,
        },
        xaxis: {
            categories: chartLabelsDaily,
            labels: {
                show: true,
                style: {
                    fontFamily: "Inter, sans-serif",
                    cssClass: 'text-xs font-normal fill-gray-500 dark:fill-gray-400'
                }
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            show: false,
        },
        fill: {
            opacity: 1,
            colors: ['#3B82F6'] // Warna biru untuk bar
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        renderPaymentMethodChart(paymentMethodDistributionData);

        if (document.getElementById("monthly-daily-revenue-chart") && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(document.getElementById("monthly-daily-revenue-chart"), dailyRevenueChartOptions);
            chart.render();
        }

        const monthSelect = document.getElementById('report-month');
        const yearSelect = document.getElementById('report-year');

        monthSelect.addEventListener('change', () => {
            document.getElementById('month-year-form').submit();
        });
        yearSelect.addEventListener('change', () => {
            document.getElementById('month-year-form').submit();
        });
    });
</script>
@endsection