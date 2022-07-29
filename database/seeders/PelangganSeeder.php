<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PelangganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $Pelanggan = [];
        $Pelanggan[] = [
            'nama' => 'Pelanggan 1',
            'telp' => '08' . rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No ' . rand(10, 99) . ' Blok ' . rand(10, 99),
        ];
        $Pelanggan[] = [
            'nama' => 'Pelanggan 2',
            'telp' => '08' . rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No ' . rand(10, 99) . ' Blok ' . rand(10, 99),
        ];
        $Pelanggan[] = [
            'nama' => 'Pelanggan 3',
            'telp' => '08' . rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No ' . rand(10, 99) . ' Blok ' . rand(10, 99),
        ];
        \App\Models\Pelanggan::insert($Pelanggan);
    }
}
