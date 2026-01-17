<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo user test với password rõ ràng
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password123'),
                'age' => 30,
                'gender' => 'male',
                'conditions' => ['hypertension', 'sinusitis'],
            ]
        );

        // Tạo thêm một user test khác
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'age' => 35,
                'gender' => 'female',
                'conditions' => [],
            ]
        );

        $this->command->info('Test users created:');
        $this->command->info('  - test@example.com / password123');
        $this->command->info('  - admin@example.com / admin123');
    }
}
