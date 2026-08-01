<?php

namespace Database\Seeders;

use App\Jobs\SeedSampleDataJob;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        SeedSampleDataJob::dispatch()->onQueue('seeding');
    }
}
