<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->input = $this->loadSettings();

        $this->input->each(function ($settingValue, $settingName) {
            Setting::create([
                'name' => $settingName,
                'value' => $settingValue
            ]);
        });
    }

    public function loadSettings(): Collection
    {
        return collect(json_decode(Storage::get('settings.json'), true))->recursive();
    }
}
