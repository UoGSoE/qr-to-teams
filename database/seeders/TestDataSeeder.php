<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Webhook;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $admin = User::factory()->create(['username' => 'admin', 'password' => bcrypt('secret')]);
        Webhook::factory()->times(10)->create();
    }
}
