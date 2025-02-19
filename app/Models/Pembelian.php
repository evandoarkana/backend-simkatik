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
        'unit',
        'harga_beli',
        'total_harga',
        'tanggal_dibeli'
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'produk_id');
    }
}
