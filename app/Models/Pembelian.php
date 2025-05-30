<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{

    protected $table = 'pembelian';

    use HasFactory;

    protected $fillable = [
        'produk_id',
        'jumlah',
        'satuan',
        'isi_perbox',
        'harga_beli',
        'total_harga',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
