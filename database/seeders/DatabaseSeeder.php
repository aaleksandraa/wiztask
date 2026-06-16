<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@wiztask.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        $defaults = [
            'app_name' => 'WizTask',
            'default_currency' => 'KM',
            'default_hourly_rate' => '40',
            'allowed_file_types' => 'jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip',
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
