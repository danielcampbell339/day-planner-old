<?php

namespace Database\Seeders;

use App\Models\Tracker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TrackerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $input = $this->loadTrackerFromJson();

        $input->each(function ($tracker, $trackerName) {
            $type = null;

            if ($tracker->has('max') && !$tracker->has('measurement')) {
                $type = 'max';
            } elseif ($tracker->has('max') && $tracker->has('measurement')) {
                $type = 'divide';
            } else {
                $type = 'increment';
            }

            Tracker::create([
                'name' => $trackerName,
                'type' => $type,
                'max' => $tracker->get('max'),
                'measurement' => $tracker->get('measurement'),
                'unit' => $tracker->get('unit'),
                'sound' => $tracker->get('sound'),
                'increment' => $tracker->get('increment'),
                'disabled' => $tracker->get('disabled') ?? 0
            ]);
        });
    }

    public function loadTrackerFromJson(): Collection
    {
        return collect(json_decode(Storage::get('tracker.json'), true))->recursive();
    }
}
