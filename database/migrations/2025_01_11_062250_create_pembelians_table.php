<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_produk');
            $table->integer('unit');
            $table->integer('harga_beli');
            $table->integer('total_harga');
            $table->dateTime('tanggal_dibeli');
            $table->timestamps();

            $table->foreign('id_produk')->references('id')->on('produk')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian');
    }
};
