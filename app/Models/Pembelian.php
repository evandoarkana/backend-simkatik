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
        'quantity',
        'satuan',
        'isi_perbox',
        'total_harga',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
