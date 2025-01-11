<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembelian</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 26px;
            margin-bottom: 10px;
            color: #4a4a4a;
        }

        .subtitle {
            text-align: center;
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }

        .footer p {
            margin: 5px 0;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 200px;
            height: auto;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-section p {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="logo-container">
        <img src="{{ public_path('images/logo-simkatik.png') }}" alt="Logo">
    </div>

    <h1>Laporan Pembelian Produk</h1>

    <p class="subtitle">
        @if (isset($bulan) && isset($tahun))
            Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
        @elseif(isset($tahun))
            Tahun: {{ $tahun }}
        @else
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Unit</th>
                <th>Harga Beli</th>
                <th>Total Harga</th>
                <th>Tanggal Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_harga = 0;
            @endphp
            @foreach ($pembelian as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_dibeli)->format('d-m-Y') }}</td>
                </tr>
                @php
                    $total_harga += $item->total_harga;
                @endphp
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p>Total Pembelian: Rp {{ number_format($total_harga, 0, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>SIMKATIK - Sistem Manajemen Toko Kosmetik</p>
        <p>&copy; {{ date('Y') }} All Rights Reserved</p>
    </div>

</body>

</html>
