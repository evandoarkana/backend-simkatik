<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk')->unique();
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
            $table->integer('stok')->default(0);
            $table->integer('harga_jual');
            $table->string('gambar_produk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
