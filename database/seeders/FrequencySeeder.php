<?php

namespace Database\Seeders;

use App\Models\Frequency;
use Illuminate\Database\Seeder;

class FrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            'week',
            'month'
        ];

        foreach ($types as $type) {
            Frequency::factory()->create([
                'name' => $type
            ]);
        }
    }
}
