<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{

    protected $table = 'Pembelian';

    use HasFactory;

    protected $fillable = [
        'id_produk',
        'unit',
        'harga_beli',
        'total_harga',
        'tanggal_dibeli'
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
