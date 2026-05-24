<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'city' => 'Riyadh',
        ]);

        $service1 = Service::create([
            'name' => 'Plumbing Inspection',
            'category' => 'plumbing',
            'description' => 'A standard plumbing inspection.',
            'price' => 100.00,
            'duration_minutes' => 60,
            'is_available' => true,
        ]);

        $service2 = Service::create([
            'name' => 'AC Cleaning',
            'category' => 'hvac',
            'description' => 'Cleaning of split and window AC units.',
            'price' => 150.00,
            'duration_minutes' => 90,
            'is_available' => true,
        ]);

        $package = Package::create([
            'name' => 'Home Maintenance Bundle',
            'description' => 'Plumbing inspection and AC cleaning included.',
            'price' => 200.00,
        ]);

        $package->services()->attach([$service1->id, $service2->id]);
    }
}
