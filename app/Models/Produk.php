<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'kategori_id',
        'stok',
        'harga_jual',
        'harga_beli',
        'diskon',
        'gambar_produk'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class);
    }

}
