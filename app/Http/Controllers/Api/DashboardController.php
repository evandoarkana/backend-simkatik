<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penjualan;
use App\Models\Produk;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getLabaBersihBulanan(Request $request)
    {
        $bulan = $request->query('bulan', Carbon::now()->month);
        $tahun = $request->query('tahun', Carbon::now()->year);

        $labaBersih = Penjualan::whereYear('tanggal_terjual', $tahun)
            ->whereMonth('tanggal_terjual', $bulan)
            ->sum('keuntungan');

        $bulanNama = Carbon::createFromFormat('m', $bulan)->format('F');

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'bulan' => $bulanNama,
                'tahun' => $tahun,
                'laba_bersih' => $labaBersih,
                'laba_bersih_format' => 'Rp ' . number_format($labaBersih, 0, ',', '.')
            ]
        ]);
    }

    public function getLabaBersihTahunan(Request $request)
    {
        $tahun = $request->query('tahun', Carbon::now()->year);

        $labaBersih = Penjualan::whereYear('tanggal_terjual', $tahun)
            ->sum('keuntungan');

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'tahun' => $tahun,
                'laba_bersih' => $labaBersih,
                'laba_bersih_format' => 'Rp ' . number_format($labaBersih, 0, ',', '.')
            ]
        ]);
    }

    public function getTotalProduk()
    {
        $totalProduk = Produk::count();

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'total_produk' => $totalProduk
            ]
        ]);
    }

    public function getTotalProdukTerjual()
    {
        $totalTerjual = Penjualan::sum('unit');

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'total_produk_terjual' => $totalTerjual
            ]
        ]);
    }

    public function getSemuaStatistik(Request $request)
    {
        $bulan = $request->query('bulan', Carbon::now()->month);
        $tahun = $request->query('tahun', Carbon::now()->year);

        $labaBulanan = Penjualan::whereYear('tanggal_terjual', $tahun)
            ->whereMonth('tanggal_terjual', $bulan)
            ->sum('keuntungan');

        $labaTahunan = Penjualan::whereYear('tanggal_terjual', $tahun)
            ->sum('keuntungan');

        $totalProduk = Produk::count();

        $totalTerjual = Penjualan::sum('unit');

        $bulanNama = Carbon::createFromFormat('m', $bulan)->format('F');

        return response()->json([
            'status' => 'sukses',
            'data' => [
                'laba_bulan_ini' => [
                    'nilai' => $labaBulanan,
                    'format' => 'Rp ' . number_format($labaBulanan, 0, ',', '.'),
                    'bulan' => $bulanNama,
                ],
                'laba_tahun_ini' => [
                    'nilai' => $labaTahunan,
                    'format' => 'Rp ' . number_format($labaTahunan, 0, ',', '.'),
                    'tahun' => $tahun
                ],
                'total_produk' => $totalProduk,
                'total_produk_terjual' => $totalTerjual
            ]
        ]);
    }
}
