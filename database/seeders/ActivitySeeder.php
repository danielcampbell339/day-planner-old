<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityCommand;
use App\Models\ActivityFrequency;
use App\Models\Command;
use App\Models\Frequency;
use App\Models\Type;
use Illuminate\Support\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ActivitySeeder extends Seeder
{
    private Collection $input;
    private Collection $types;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->input = $this->loadActivities();
        $this->types = Type::all();

        $this->types->each(function ($type) {
            if (empty($this->input->get($type->name))) {
                return true;
            }

            $this->input->get($type->name)->each(function ($activity, $key) use ($type) {
                $this->createActivityRecord($type, $activity);
            });
        });
    }

    public function loadActivities(): Collection
    {
        return collect(json_decode(Storage::get('activities.json'), true))->recursive();
    }

    public function createActivityRecord(Type $type, Collection $activity, Activity $parent = null)
    {
        $limitMin = null;
        $limitMax = null;

        if ($activity->has('limit')) {
            $limitMin = $activity->get('limit')->get('min');
            $limitMax = $activity->get('limit')->get('max');
        }

        $days = null;
        if ($activity->has('days')) {
            $days = $this->getDaysFromActivity($activity);
        }

        $start = null;
        $end = null;
        if ($activity->has('time')) {
            $start = $activity->get('time')->get('start');
            $end = $activity->get('time')->get('end');
        }

        $createdActivity = Activity::create([
            'type_id' => $type->id,
            'parent_activity_id' => $parent->id ?? null,
            'name' => $activity->get('name'),
            'minutes' => $activity->get('minutes') ?? $parent->minutes ?? null,
            'limit_min' => $limitMin,
            'limit_max' => $limitMax,
            'days' => $days,
            'disabled' => $activity->get('disabled') ?? 0,
            'start' => $start,
            'end' => $end,
            'date' => $activity->get('date') ?? null,
            'priority' => $activity->get('priority') ?? null
        ]);

        $this->createFrequencyRecords($activity, $createdActivity);
        $this->createCommandRecords($activity, $createdActivity);
        $this->createListRecords($type, $activity, $createdActivity);
    }

    public function createFrequencyRecords(Collection $activity, Activity $createdActivity)
    {
        if (!$activity->has('frequency')) {
            return;
        }

        ActivityFrequency::create([
            'activity_id' => $createdActivity->id,
            'frequency_id' => Frequency::where(
                'name',
                $activity->get('frequency')->get('type')
            )->first()->id,
            'amount' => $activity->get('frequency')->get('amount') ?? 1,
        ]);
    }

    public function createCommandRecords(Collection $activity, Activity $createdActivity)
    {
        if (!$activity->has('commands')) {
            return;
        }

        $activity->get('commands')->each(function ($command, $key) use ($createdActivity) {
            $createdCommand = Command::firstOrCreate(['name' => $command]);

            ActivityCommand::create([
                'activity_id' => $createdActivity->id,
                'command_id' => $createdCommand->id
            ]);
        });
    }

    public function createListRecords(Type $type, Collection $activity, Activity $parentActivity)
    {
        if (!$activity->has('list')) {
            return;
        }

        $activity->get('list')->each(function ($childActivity, $key) use ($type, $parentActivity) {
            $childActivity->put(
                'name',
                $parentActivity->name . ' - ' . $childActivity->get('name')
            );

            $this->createActivityRecord($type, $childActivity, $parentActivity);
        });
    }

    public function getDaysFromActivity(Collection $activity)
    {
        if (!$activity->has('days')) {
            return;
        }

        $days = $activity->get('days');

        if ($days == "weekend") {
            $days = [
                'Saturday',
                'Sunday'
            ];
        }

        if ($days == "weekday") {
            $days = [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday'
            ];
        }

        return collect($days);
    }
}
