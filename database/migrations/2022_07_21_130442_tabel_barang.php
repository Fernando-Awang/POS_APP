<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TabelBarang extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kategori_barang')->nullable();
            $table->string('nama');
            $table->integer('harga_jual')->nullable();
            $table->integer('harga_beli')->nullable();
            $table->integer('stok')->nullable();
            $table->foreign('id_kategori_barang')->references('id')->on('kategori_barang');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang');
    }
}
