<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\KeyNote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user (idempotent)
        $adminEmail = env('ADMIN_EMAIL', 'notas@example.com');
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        // Clear to avoid duplicates
        $admin->notes()->delete();
        $admin->keyNotes()->delete();

        // Create some tags
        \App\Models\Tag::factory()->count(10)->create(['user_id' => $admin->id]);

        // Create some normal notes
        Note::factory()->count(10)->create(['user_id' => $admin->id]);

        // Create some key notes
        KeyNote::factory()->count(5)->create(['user_id' => $admin->id]);

        // Create a special memory key note
        KeyNote::create([
            'user_id' => $admin->id,
            'key' => 'memory',
            'title' => 'Core Memory',
            'content' => 'The assistant should remember the user\'s preferences.',
        ]);
    }
}
