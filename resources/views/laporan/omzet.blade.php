<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Omzet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        h1 {
            font-size: 2.5em;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        h2 {
            font-size: 1.5em;
            color: #34495e;
            margin-top: 25px;
        }
        .report-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #e0e0e0;
        }
        .report-section:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
        }
        .report-section p {
            margin: 5px 0;
            padding-left: 20px;
            line-height: 1.6;
        }
        .separator {
            border-top: 1px solid #bbb;
            margin: 10px 0;
            width: 80%;
            margin-left: 20px;
        }
        .total-line {
            font-weight: bold;
            color: #2980b9;
        }
        .total-summary {
            margin-top: 30px;
            border-top: 3px solid #2c3e50;
            padding-top: 20px;
            text-align: right;
        }
        .total-summary p {
            font-size: 1.1em;
            margin: 8px 0;
            font-weight: 500;
        }
        .total-summary .grand-total {
            font-size: 1.5em;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 15px;
        }
        .date-filter {
            text-align: center;
            margin-bottom: 30px;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        .date-filter label {
            margin-right: 10px;
            font-weight: bold;
            color: #34495e;
        }
        .date-filter input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 15px;
            font-size: 1em;
            color: #555;
        }
        .date-filter button {
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }
        .date-filter button:hover {
            background-color: #219d54;
        }
        .period-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.1em;
            color: #555;
        }
        .period-total-info {
            font-size: 1.1em;
            color: #555;
            margin-top: 10px;
            font-weight: bold; /* Menambahkan bold agar lebih terlihat */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>LAPORAN OMZET</h1>

        <div class="date-filter">
            <form action="{{ route('report.omzet') }}" method="GET">
                <label for="start_date">Dari Tanggal:</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                <label for="end_date">Sampai Tanggal:</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                <button type="submit">Filter</button>
            </form>
        </div>

        <p class="period-info">Periode Laporan Ini: {{ $startDate->format('d/m/Y') }} @if(!$startDate->eq($endDate)) - {{ $endDate->format('d/m/Y') }} @endif</p>
        <br>

        @php
            $counter = 1;
            $categoryOrder = ['Billiard', 'minuman', 'makanan', 'rokok', 'alat'];
        @endphp

        @foreach ($categoryOrder as $categoryKey)
            @if (isset($reportData[$categoryKey]))
                <div class="report-section">
                    @php
                        $displayCategory = ucfirst($categoryKey);
                        if ($displayCategory === 'Alat') {
                            $displayCategory = 'Sarung Tangan';
                        }
                    @endphp
                    <h2>{{ $counter++ }}. {{ $displayCategory }}</h2>
                    <p>- Cash: Rp. {{ number_format($reportData[$categoryKey]['Cash'], 0, ',', '.') }}</p>
                    <p>- QRIS: Rp. {{ number_format($reportData[$categoryKey]['QRIS'], 0, ',', '.') }}</p>
                    <div class="separator"></div>
                    <p class="total-line">- Total: Rp. {{ number_format($reportData[$categoryKey]['Cash'] + $reportData[$categoryKey]['QRIS'], 0, ',', '.') }}</p>
                </div>
            @endif
        @endforeach

        <div class="total-summary">
            <p>Total Cash: Rp. {{ number_format($totalOverallCash, 0, ',', '.') }}</p>
            <p>Total QRIS: Rp. {{ number_format($totalOverallQris, 0, ',', '.') }}</p>
            <hr>
            <p class="grand-total">Total Omzet: Rp. {{ number_format($totalOverallOmzet, 0, ',', '.') }}</p>

            <p class="period-total-info">
                Total Omzet tanggal {{ $firstDayOfMonth->format('j F Y') }} sampai {{ $endDate->format('j F Y') }} adalah Rp. {{ number_format($totalOmzetRangePeriod, 0, ',', '.') }}
            </p>
        </div>
    </div>
</body>
</html>