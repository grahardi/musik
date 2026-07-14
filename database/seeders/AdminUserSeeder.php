<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Bikin 1 akun admin awal. GANTI PASSWORD-NYA setelah login pertama kali.
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'admin@musik.raisa.id'],
            [
                'name' => 'Admin',
                'password' => Hash::make('GantiSegera123!'),
            ]
        );

        $this->command->warn('Akun admin dibuat: admin@musik.raisa.id / GantiSegera123!  -- SEGERA GANTI PASSWORD INI.');
    }
}
