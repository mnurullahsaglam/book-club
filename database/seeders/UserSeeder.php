<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Mehmet Nurullah Sağlam',
            'email' => 'nurullahsl87@gmail.com',
            'password' => bcrypt('password'),
            'is_admin' => true,
        ]);

        User::create([
            'name' => 'Ömer Faruk Maden',
            'email' => 'o.farukmaden@gmail.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Oktay Kaymak',
            'email' => 'oktaykaymak02@gmail.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Talha Saraç',
            'email' => 'talhasaracc@gmail.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Muhsin Hasib Başpehlivan',
            'email' => 'muhsinbaspehlivan@hotmail.com',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Emre Göçebe',
            'email' => 'emre.gocebe@kitap.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

    }
}
