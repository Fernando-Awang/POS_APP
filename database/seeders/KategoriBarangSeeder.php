<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class KategoriBarangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $KategoriBarang = [];
        $namaKategori = [
            'Kategori 1',
            'Kategori 2',
        ];
        foreach ($namaKategori as $nama) {
            array_push($KategoriBarang, [
                'nama' => $nama,
            ]);
        }
        \App\Models\KetegoriBarang::insert($KategoriBarang);
    }
}
