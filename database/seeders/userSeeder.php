<?php

namespace Database\Seeders;

use App\Models\DetailUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dataUser = User::create([
            'username' => 'owner1',
            'password' => Hash::make('123'),
            'role' => 'owner',
        ]);
        $dataDetailUser = DetailUser::create([
            'id_user' => $dataUser->id,
            'nama' => 'Owner 1',
        ]);
    }
}
