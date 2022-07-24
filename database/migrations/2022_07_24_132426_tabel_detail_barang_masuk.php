<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TabelDetailBarangMasuk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_barang_masuk')->nullable();
            $table->foreignId('id_barang')->nullable();
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('subtotal');
            $table->foreign('id_barang_masuk')->references('id')->on('barang_masuk')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id')->on('barang')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detail_barang_masuk');
    }
}
