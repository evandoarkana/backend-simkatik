<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
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

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 200px;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            table-layout: fixed;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th {
            background-color: #f8f8f8;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 12px;
        }

        td {
            padding: 10px;
            text-align: left;
            word-wrap: break-word;
        }

        td.text-right {
            text-align: right;
        }

        td.text-center {
            text-align: center;
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

        .total-keuntungan {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            padding-right: 20px;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="{{ public_path('images/logo-simkatik.png') }}" alt="Logo">
    </div>
    <h1>Laporan Penjualan Produk</h1>
    <p class="subtitle">
        @if (isset($bulan) && isset($tahun))
            Periode: {{ \Carbon\Carbon::create($tahun, $bulan)->translatedFormat('F Y') }}
        @elseif (isset($tahun))
            Tahun: {{ $tahun }}
        @else
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Unit</th>
                <th>Harga Jual</th>
                <th>Total Harga</th>
                <th>Keuntungan</th>
                <th>Tanggal Dibeli</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_keuntungan = 0;
            @endphp
            @foreach ($penjualan as $item)
                <tr>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->keuntungan, 0, ',', '.') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_terjual)->format('d-m-Y') }}</td>
                </tr>
                @php
                    $total_keuntungan += $item->keuntungan; 
                @endphp
            @endforeach
        </tbody>
    </table>

    <div class="total-keuntungan">
        <p>Total Keuntungan: Rp {{ number_format($total_keuntungan, 0, ',', '.') }}</p>
    </div>

    <div class="footer">
        <p>SIMKATIK - Sistem Manajemen Toko Kosmetik</p>
        <p>&copy; {{ date('Y') }} All Rights Reserved</p>
    </div>
</body>

</html>
