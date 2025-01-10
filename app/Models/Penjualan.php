<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';

    use HasFactory;

    protected $fillable = [
        'id_produk', 'unit', 'harga_jual', 'total_harga', 'keuntungan', 'tanggal_terjual'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
