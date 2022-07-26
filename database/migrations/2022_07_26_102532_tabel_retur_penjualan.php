<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TabelReturPenjualan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retur_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penjualan')->nullable();
            $table->foreignId('id_barang')->nullable();
            $table->foreignId('id_user')->nullable();
            $table->integer('jumlah');
            $table->foreign('id_penjualan')->references('id')->on('penjualan')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id')->on('barang')->cascadeOnDelete();
            $table->foreign('id_user')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retur_penjualan');
    }
}
