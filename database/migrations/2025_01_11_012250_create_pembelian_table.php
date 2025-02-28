<?php

use App\Enums\Satuan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->integer('jumlah')->default(1);
            $table->enum('satuan', Satuan::values());
            $table->integer('isi_perbox')->nullable();
            $table->integer('harga_beli')->default(0);
            $table->integer('total_harga')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pembelian');
    }
};
