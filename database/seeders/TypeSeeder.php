<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'first',
            ],
            [
                'name' => 'general',
                'sortable' => true,
                'shuffleable' => true
            ],
            [
                'name' => 'last',
                'reversable' => true
            ],
            [
                'name' => 'extra',
                'sortable' => true,
                'shuffleable' => true
            ],
            [
                'name' => 'time_activities',
                'reversable' => true
            ]
        ];

        foreach ($types as $type) {
            Type::factory()->create($type);
        }
    }
}
