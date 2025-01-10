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

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 200px;
            /* Ukuran logo */
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
            table-layout: fixed;
            /* Mengatur agar kolom memiliki lebar tetap */
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
            /* Header rata tengah */
            padding: 12px;
            white-space: nowrap;
            /* Mencegah teks header terpotong */
        }

        td {
            padding: 10px;
            text-align: left;
            word-wrap: break-word;
            /* Membungkus teks panjang */
        }

        /* Kolom spesifik */
        td.text-right {
            text-align: right;
        }

        td.text-center {
            text-align: center;
        }

        td img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Lebar kolom disesuaikan */
        th:first-child,
        td:first-child {
            width: 15%;
            /* Gambar */
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 30%;
            /* Nama Produk */
        }

        th:nth-child(3),
        td:nth-child(3),
        th:nth-child(4),
        td:nth-child(4),
        th:nth-child(5),
        td:nth-child(5) {
            width: 15%;
            /* Harga Jual, Harga Beli, Stok */
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
    </style>
</head>

<body>
    <!-- Logo dan Header -->
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
                    <td class="text-center">
                        @if ($item->gambar_produk && file_exists(public_path('storage/' . $item->gambar_produk)))
                            <img src="{{ public_path('storage/' . $item->gambar_produk) }}" alt="Gambar Produk">
                        @else
                            <img src="{{ public_path('images/default.png') }}" alt="Gambar Default">
                        @endif
                    </td>
                    <td>{{ $item->nama_produk }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->stok }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>SIMKATIK - Sistem Manajemen Toko Kosmetik</p>
        <p>&copy; {{ date('Y') }} All Rights Reserved</p>
    </div>
</body>

</html>
