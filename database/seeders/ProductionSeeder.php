<?php

namespace Database\Seeders;

use App\Models\KeyNote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * This creates only the Admin user and the core 'memory' key note without dummy data.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'notas@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make($adminPassword),
            ]
        );

        KeyNote::firstOrCreate(
            [
                'user_id' => $admin->id,
                'key' => 'memory',
            ],
            [
                'title' => 'Memoria Principal / Core Memory',
                'content' => 'Bloque de memoria inicial para el asistente. / Initial memory block for the assistant.',
            ]
        );

        $this->command->info("Production Seeder completed.");
        $this->command->info("Admin email: {$adminEmail}");
    }
}
