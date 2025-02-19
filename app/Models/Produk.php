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
        'pembelian_id',
        'stok',
        'harga_jual',
        'gambar_produk'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id');
    }



    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

}
