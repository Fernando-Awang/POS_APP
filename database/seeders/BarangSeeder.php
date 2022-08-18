<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $barang = [];
        $barang[] = [
            'nama' => 'Barang 1',
            'kode_produk' => 'BRG-1',
            'id_kategori_barang' => 1,
            'harga_jual' => 15000,
            'harga_beli' => 10000,
            'stok' => 100,
        ];
        $barang[] = [
            'nama' => 'Barang 2',
            'kode_produk' => 'BRG-2',
            'id_kategori_barang' => 1,
            'harga_jual' => 10000,
            'harga_beli' => 9000,
            'stok' => 100,
        ];
        $barang[] = [
            'nama' => 'Barang 3',
            'kode_produk' => 'BRG-3',
            'id_kategori_barang' => 2,
            'harga_jual' => 12000,
            'harga_beli' => 10000,
            'stok' => 100,
        ];
        $barang[] = [
            'nama' => 'Barang 4',
            'kode_produk' => 'BRG-4',
            'id_kategori_barang' => 2,
            'harga_jual' => 15000,
            'harga_beli' => 10000,
            'stok' => 100,
        ];
        $barang[] = [
            'nama' => 'Barang 5',
            'kode_produk' => 'BRG-5',
            'id_kategori_barang' => 2,
            'harga_jual' => 20000,
            'harga_beli' => 15000,
            'stok' => 100,
        ];
        \App\Models\Barang::insert($barang);
    }
}
