<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        (new TypeSeeder())->run();
        (new FrequencySeeder())->run();
        (new ActivitySeeder())->run();
        (new SettingSeeder())->run();
        (new TrackerSeeder())->run();
    }
}
