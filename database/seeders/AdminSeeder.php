<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Varsayılan admin kullanıcısını oluşturur (zaten varsa dokunmaz).
     * Giriş sonrası şifreyi mutlaka değiştirin.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@opale.com'],
            [
                'name' => 'Admin',
                'mobile' => '5000000000',
                'password' => Hash::make('Admin12345'),
                'utype' => 'ADM',
            ]
        );
    }
}
