<?php

namespace Database\Seeders;

use App\Enums\SubscriptionType;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

// initialize global subscription types
class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SubscriptionType::all() as $type) {
            Subscription::firstOrCreate(['type' => $type]);
        }
    }
}
