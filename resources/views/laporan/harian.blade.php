@extends('default')

@section('title', 'Laporan Harian')

@section('content')

<?php
$penyewaansCollection = collect($penyewaans);

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

$headers = [
    'ID', 'Nama Penyewa', 'Meja', 'Durasi (Jam)', 'Harga/Jam',
    'Total Layanan', 'Total Bayar', 'Waktu Mulai', 'Waktu Selesai', 'Status', 'Metode Bayar'
];
?>

<div class="container mx-auto p-4 md:p-6 bg-[#121212] text-white min-h-screen">
    <h1 class="text-3xl font-bold mb-6 text-white">Laporan Harian</h1>

    <form id="date-form" action="{{ route('laporan.harian') }}" method="GET" class="mb-6 flex flex-col md:flex-row items-center gap-4">
        <label for="report-date" class="block text-sm font-medium text-gray-300">Pilih Tanggal:</label>
        <div class="relative max-w-sm">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                </svg>
            </div>
            {{-- Pakai date bawaan browser --}}
            <input type="date" id="report-date" name="date" value="{{ $date }}"
                   class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5">
        </div>
        <button type="submit" class="hidden">Submit</button>
    </form>

    {{-- Total Pendapatan --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md flex items-center justify-between border border-gray-700">
            <div>
                <p class="text-sm font-medium text-gray-400">Total Pendapatan (Nett)</p>
                <p id="total-pendapatan" class="text-3xl font-bold text-white mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-blue-900 rounded-full">
                <i class="fa-solid fa-rupiah-sign text-blue-300 text-2xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-[#1e1e1e] p-6 rounded-lg shadow-md border border-gray-700">
            <h2 class="text-xl font-semibold mb-4 text-white">Distribusi Pendapatan</h2>
            <div class="chart-container w-full h-80 flex items-center justify-center">
                <canvas id="paymentMethodChart"></canvas>
            </div>
            <p class="text-sm text-gray-400 mt-4 italic">
                *Data distribusi Cash & QRIS diambil dari kolom 'is_qris'.
            </p>
        </div>
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Pendapatan Per Meja</h2>
    <div id="pendapatan-meja-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-8">
        @forelse ($pendapatanPerMeja as $meja)
            <div class="bg-[#1e1e1e] p-4 rounded-lg shadow-md border border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                <h3 class="text-lg font-semibold text-white">{{ $meja['nama_meja'] }}</h3>
                <p class="text-xl font-bold text-green-400 mt-2">Rp {{ number_format($meja['total_pendapatan'], 0, ',', '.') }}</p>
            </div>
        @empty
            <p class="text-gray-400 col-span-full">Tidak ada pendapatan tercatat untuk meja pada tanggal ini.</p>
        @endforelse
    </div>

    <h2 class="text-xl font-semibold mb-4 text-white">Detail Penyewaan</h2>
    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-700 text-gray-300">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-6 py-3">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($penyewaansCollection as $penyewaan)
                    <tr class="bg-[#1e1e1e] border-b border-gray-700 hover:bg-[#232323] transition-colors duration-200">
                        <td class="px-6 py-4">{{ $penyewaan->id }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->nama_penyewa }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->meja->nama_meja ?? $penyewaan->meja_id }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->durasi_jam }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->harga_per_jam, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->total_service, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ number_format($penyewaan->total_bayar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->waktu_mulai->format('H:i:s') }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->waktu_selesai ? $penyewaan->waktu_selesai->format('H:i:s') : '-' }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->status }}</td>
                        <td class="px-6 py-4">{{ $penyewaan->is_qris ? 'QRIS' : 'Cash' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($headers) }}" class="px-6 py-4 text-center text-gray-400">Tidak ada data penyewaan untuk tanggal ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('script')
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

    document.addEventListener('DOMContentLoaded', () => {
        renderPaymentMethodChart(paymentMethodDistributionData);

        // auto submit saat tanggal dipilih
        const dateInput = document.getElementById('report-date');
        dateInput.addEventListener('change', () => {
            document.getElementById('date-form').submit();
        });
    });
</script>
@endsection
