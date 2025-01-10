<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 150px;
        }

        .header h1 {
            margin: 10px 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            margin: 0;
            color: #7f8c8d;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('images/logo-simkatik.png') }}" alt="Logo SIMKATIK">
        <h1>Sistem Manajemen Toko Kosmetik</h1>
        <p>Laporan Data Produk</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Harga Jual</th>
                <th>Harga Beli</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $produk->nama_produk }}</td>
                <td>Rp {{ number_format($produk->harga_jual, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($produk->harga_beli, 0, ',', '.') }}</td>
                <td>{{ $produk->stok }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
