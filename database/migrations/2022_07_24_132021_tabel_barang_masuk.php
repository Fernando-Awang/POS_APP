<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TabelBarangMasuk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->nullable();
            $table->foreignId('id_user')->nullable();
            $table->foreignId('id_supplier')->nullable();
            $table->dateTime('tanggal');
            $table->string('keterangan')->nullable();
            $table->foreign('id_user')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('id_supplier')->references('id')->on('supplier')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barang_masuk');
    }
}
