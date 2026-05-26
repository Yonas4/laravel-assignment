<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PaymentGateway;
use App\Enums\PaymentModule;
use App\Enums\TransactionStatus;
use App\Models\Package;
use App\Models\PaymentTransaction;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ──────────────────────────────────────────────────
        // USERS
        // ──────────────────────────────────────────────────
        $demoUser = User::create([
            'name' => 'Demo User',
            'email' => 'demo@ajeer.app',
            'password' => Hash::make('password'),
            'phone' => '+966500000000',
            'city' => 'Riyadh',
            'status' => 'active',
        ]);

        // ──────────────────────────────────────────────────
        // SUBSCRIPTION PLANS (3: trial + basic + pro)
        // ──────────────────────────────────────────────────
        $trialPlan = SubscriptionPlan::create([
            'slug' => 'trial',
            'name' => 'Free Trial',
            'description' => '14-day free trial with full access.',
            'price' => 0,
            'currency' => 'SAR',
            'duration_days' => 14,
            'is_trial' => true,
            'features' => ['full_access', 'limited_bookings'],
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'slug' => 'basic',
            'name' => 'Basic Plan',
            'description' => 'Monthly basic subscription.',
            'price' => 49.00,
            'currency' => 'SAR',
            'duration_days' => 30,
            'is_trial' => false,
            'features' => ['full_access', 'priority_support'],
            'is_active' => true,
        ]);

        SubscriptionPlan::create([
            'slug' => 'pro',
            'name' => 'Pro Plan',
            'description' => 'Monthly pro subscription with unlimited access.',
            'price' => 99.00,
            'currency' => 'SAR',
            'duration_days' => 30,
            'is_trial' => false,
            'features' => ['unlimited_access', 'priority_support', 'dedicated_agent'],
            'is_active' => true,
        ]);

        // ──────────────────────────────────────────────────
        // SERVICE CATEGORIES (4)
        // ──────────────────────────────────────────────────
        $plumbing = ServiceCategory::create([
            'name' => 'Plumbing',
            'description' => 'All plumbing related services.',
            'icon' => 'plumbing',
            'is_active' => true,
        ]);

        $electrical = ServiceCategory::create([
            'name' => 'Electrical',
            'description' => 'Electrical installation and repair.',
            'icon' => 'electrical',
            'is_active' => true,
        ]);

        $cleaning = ServiceCategory::create([
            'name' => 'Cleaning',
            'description' => 'Professional cleaning services.',
            'icon' => 'cleaning',
            'is_active' => true,
        ]);

        $acCooling = ServiceCategory::create([
            'name' => 'AC & Cooling',
            'description' => 'Air conditioning and cooling services.',
            'icon' => 'ac_cooling',
            'is_active' => true,
        ]);

        // ──────────────────────────────────────────────────
        // SERVICES (2 per category = 8 total)
        // ──────────────────────────────────────────────────
        $service1 = Service::create([
            'category_id' => $plumbing->id,
            'name' => 'Plumbing Inspection',
            'description' => 'A standard plumbing inspection.',
            'price' => 100.00,
            'currency' => 'SAR',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $service2 = Service::create([
            'category_id' => $plumbing->id,
            'name' => 'Pipe Repair',
            'description' => 'Repair of leaking or damaged pipes.',
            'price' => 200.00,
            'currency' => 'SAR',
            'duration_minutes' => 90,
            'is_active' => true,
        ]);

        $service3 = Service::create([
            'category_id' => $electrical->id,
            'name' => 'Electrical Wiring',
            'description' => 'New electrical wiring installation.',
            'price' => 300.00,
            'currency' => 'SAR',
            'duration_minutes' => 120,
            'is_active' => true,
        ]);

        Service::create([
            'category_id' => $electrical->id,
            'name' => 'Circuit Breaker Repair',
            'description' => 'Diagnosis and repair of circuit breakers.',
            'price' => 150.00,
            'currency' => 'SAR',
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        Service::create([
            'category_id' => $cleaning->id,
            'name' => 'Deep Cleaning',
            'description' => 'Full deep cleaning of home.',
            'price' => 250.00,
            'currency' => 'SAR',
            'duration_minutes' => 180,
            'is_active' => true,
        ]);

        Service::create([
            'category_id' => $cleaning->id,
            'name' => 'Office Cleaning',
            'description' => 'Professional office cleaning service.',
            'price' => 350.00,
            'currency' => 'SAR',
            'duration_minutes' => 120,
            'is_active' => true,
        ]);

        $acService1 = Service::create([
            'category_id' => $acCooling->id,
            'name' => 'AC Cleaning',
            'description' => 'Cleaning of split and window AC units.',
            'price' => 150.00,
            'currency' => 'SAR',
            'duration_minutes' => 90,
            'is_active' => true,
        ]);

        Service::create([
            'category_id' => $acCooling->id,
            'name' => 'AC Installation',
            'description' => 'New AC unit installation.',
            'price' => 500.00,
            'currency' => 'SAR',
            'duration_minutes' => 180,
            'is_active' => true,
        ]);

        // ──────────────────────────────────────────────────
        // PACKAGES (Home Care Bundle with 3 services)
        // ──────────────────────────────────────────────────
        $package = Package::create([
            'name' => 'Home Care Bundle',
            'description' => 'Plumbing inspection, electrical wiring, and AC cleaning included.',
            'price' => 450.00,
            'currency' => 'SAR',
            'is_active' => true,
        ]);

        $package->services()->attach([
            $service1->id => ['sort_order' => 1],
            $service3->id => ['sort_order' => 2],
            $acService1->id => ['sort_order' => 3],
        ]);

        // ──────────────────────────────────────────────────
        // PAYMENT TRANSACTIONS (4 sample records)
        // ──────────────────────────────────────────────────
        PaymentTransaction::create([
            'user_id' => $demoUser->id,
            'gateway' => PaymentGateway::MOYASAR->value,
            'module' => PaymentModule::SUBSCRIPTION->value,
            'amount' => 49.00,
            'currency' => 'SAR',
            'city' => 'Riyadh',
            'status' => TransactionStatus::SUCCESS->value,
            'idempotency_key' => 'seed-idem-001',
            'paid_at' => now()->subDays(5),
        ]);

        PaymentTransaction::create([
            'user_id' => $demoUser->id,
            'gateway' => PaymentGateway::TAP->value,
            'module' => PaymentModule::BOOKING->value,
            'amount' => 100.00,
            'currency' => 'SAR',
            'city' => 'Riyadh',
            'status' => TransactionStatus::SUCCESS->value,
            'idempotency_key' => 'seed-idem-002',
            'paid_at' => now()->subDays(3),
        ]);

        PaymentTransaction::create([
            'user_id' => $demoUser->id,
            'gateway' => PaymentGateway::STRIPE->value,
            'module' => PaymentModule::SUBSCRIPTION->value,
            'amount' => 99.00,
            'currency' => 'SAR',
            'city' => 'Jeddah',
            'status' => TransactionStatus::PENDING->value,
            'idempotency_key' => 'seed-idem-003',
        ]);

        PaymentTransaction::create([
            'user_id' => $demoUser->id,
            'gateway' => PaymentGateway::MOYASAR->value,
            'module' => PaymentModule::CART->value,
            'amount' => 450.00,
            'currency' => 'SAR',
            'city' => 'Dammam',
            'status' => TransactionStatus::FAILED->value,
            'idempotency_key' => 'seed-idem-004',
            'failed_at' => now()->subDays(1),
        ]);
    }
}
