<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Laporan Produk</title>
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
            text-align: center;
            /* Header tabel rata tengah */
        }

        th,
        td {
            padding: 10px;
        }

        td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
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
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="{{ public_path('images/logo-simkatik.png') }}" alt="Logo">
    </div>
    <h1>Laporan Semua Produk</h1>
    <table>
        <thead>
            <tr>
                <th>Gambar</th>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>Harga Beli</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($produk as $item)
                <tr>
                    <td>
                        @if ($item->gambar_produk && file_exists(public_path('storage/' . $item->gambar_produk)))
                            <img src="{{ public_path('storage/' . $item->gambar_produk) }}" alt="Gambar Produk">
                        @else
                            <img src="{{ public_path('images/default.png') }}" alt="Gambar Default">
                        @endif
                    </td>
                    <td>{{ $item->nama_produk }}</td>
                    <td>Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->stok }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">
        <p>SIMKATIK - Sistem Manajemen Toko Kosmetik</p>
        <p>&copy; {{ date('Y') }} - All Rights Reserved</p>
    </div>
</body>

</html>
