<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'Produk';

    use HasFactory;

    protected $fillable = [
        'gambar_produk',
        'nama_produk',
        'id_kategori',
        'harga_jual',
        'harga_beli',
        'stok',
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
}
