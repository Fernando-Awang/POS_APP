<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $supplier = [];
        $supplier[] = [
            'nama' => 'Supplier 1',
            'telp' => '08'.rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No '.rand(10,99).' Blok '.rand(10,99),
        ];
        $supplier[] = [
            'nama' => 'Supplier 2',
            'telp' => '08'.rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No '.rand(10,99).' Blok '.rand(10,99),
        ];
        $supplier[] = [
            'nama' => 'Supplier 3',
            'telp' => '08'.rand(100000000, 999999999),
            'alamat' => 'Jl. Raya No '.rand(10,99).' Blok '.rand(10,99),
        ];
        \App\Models\Supplier::insert($supplier);
    }
}
