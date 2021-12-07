<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Setting;
use App\Models\Tracker;
use App\Models\Activity;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ActivityFrequency;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\ScheduleCollection;

class ScheduleController extends Controller
{
    protected ?Carbon $startTime = null;
    protected Carbon $endTime;
    protected $activities;
    protected $tracker;
    protected $schedule;
    protected $condensed;

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // First check any frequency activities and update them if they are ready to be added again
        ActivityFrequency::updateExpiredFrequencyActivities();

        $this->startTime = $this->generateDate($request->startTime);
        $this->endTime = $this->generateDate($request->endTime);

        $this->activities = Activity::getActivities();
        $this->tracker = $this->generateTracker();
        $this->schedule = $this->buildScheduleStructure();
        $this->addTimeActivitiesToSchedule();
        $this->addActivitiesToSchedule($this->activities->get('first'));
        $this->addLastActivitiesToSchedule($this->activities->get('last'));
        $this->addActivitiesToSchedule($this->activities->get('general'), true);

        $this->generateSchedule();

        $response = new ScheduleCollection($this->schedule);

        $cacheKey = now()->toDateString();
        Cache::put($cacheKey, $response->toJson(), now()->addDay(1));

        return $response;
    }

    private function generateSchedule()
    {
        $this->fillWithFreeTime();
        $this->fillWithTracker();
        $this->createScheduleIds();
    }

    private function createScheduleIds()
    {
        for ($i = 0; $i < $this->schedule->count(); $i++) {
            if (
                $i - 1 >= 0 &&
                $this->schedule[$i - 1]->get('activity')->name == $this->schedule[$i]->get('activity')->name
            ) {
                $id = $this->schedule[$i - 1]->get('schedule_id');
            } else {
                $id = Str::uuid()->toString();
            }

            $this->schedule[$i]->put('schedule_id', $id);
        }
    }

    private function fillWithTracker()
    {
        if (!$this->tracker) {
            return;
        }

        $this->tracker->each(function ($t) {
            $scheduleIndex = $this->schedule->search(function ($sch) use ($t) {
                return $sch->get('time')->format('H:i') == $t->get('time')->format('H:i');
            });

            $this->schedule->splice($scheduleIndex, 0, [$t]);
        });
    }

    private function fillWithFreeTime()
    {
        $this->schedule->each(function ($sch, $i) {
            if (!$sch->get('activity')) {
                if ($i - 1 >= 0 && $this->schedule[$i - 1]->get('activity') && $this->schedule[$i - 1]->get('activity')->get('name') == 'Free Time') {
                    $id = $this->schedule[$i - 1]->get('activity')->id;
                } else {
                    $id = Str::uuid()->toString();
                }

                $sch->put('activity', Activity::factory()->make([
                    'id' => $id,
                    'name' => 'Free Time'
                ]));
            }
        });
    }

    private function addLastActivitiesToSchedule($activityList)
    {
        if (!$activityList) {
            return;
        }

        // Reverse the schedule
        $this->schedule = $this->schedule->reverse()->values();

        $this->schedule->each(function ($sch, $i) use ($activityList) {
            if ($sch->get('activity')) {
                return true;
            }

            if ($activityList->isEmpty()) {
                return false;
            }

            $activity = $activityList->shift();

            for ($j = 0; $j < $activity->minutes; $j++) {
                if (($i + $j) >= $this->schedule->count()) {
                    break;
                }

                $this->schedule[$i + $j]->put('activity', $activity);
            }
        });

        // Re-reverse the list
        $this->schedule = $this->schedule->reverse();
    }

    private function addActivitiesToSchedule(
        $activityList,
        $useExtra = false
    ) {
        if (!$activityList) {
            return;
        }

        $addedActivities = collect();
        $this->schedule->each(function ($sch, $i) use ($activityList, $addedActivities) {
            if ($sch->get('activity')) {
                return true;
            }

            $count = -1;
            while (!$sch->get('activity')) {
                $count++;
                if ($count >= $activityList->count()) {
                    break;
                }

                $activityList = $activityList->values();
                $activity = $activityList[$count];

                if ($addedActivities->where('id', $activity->id)->count() > 0) {
                    continue;
                }

                // We need to first make sure that there is enough time slots free
                $freeMinutes = 0;
                for ($j = 0; $j < $activity->minutes; $j++) {
                    if (($i + $j) >= $this->schedule->count()) {
                        break;
                    }

                    if ($this->schedule[$i + $j]->get('activity')) {
                        break;
                    }

                    $freeMinutes++;
                }

                if ($freeMinutes == $activity->minutes) {
                    for ($j = 0; $j < $activity->minutes; $j++) {
                        if (($i + $j) >= $this->schedule->count()) {
                            break;
                        }

                        $this->schedule[$i + $j]->put('activity', $activity);
                    }

                    $addedActivities->push($activity);
                }
            }
        });

        if ($useExtra) {
            $this->addActivitiesToSchedule($this->activities->get('extra'));
        }

        $this->schedule = $this->schedule->values();
    }

    private function addTimeActivitiesToSchedule()
    {
        if (!$this->activities->get('time_activities')) {
            return;
        }

        $this->schedule->each(function ($sch, $i) {
            $activeTimeActivity = $this
                ->activities
                ->get('time_activities')
                ->first(function ($timeActivity) use ($sch) {
                    return $timeActivity->start->eq($sch->get('time'));
                });

            if ($activeTimeActivity) {
                $activeTimeActivityMinutes = $this->calculateMinutes($activeTimeActivity);

                for ($j = 0; $j < $activeTimeActivityMinutes; $j++) {
                    if (($i + $j) >= $this->schedule->count()) {
                        break;
                    }

                    $this->schedule[$i + $j]->put('activity', $activeTimeActivity);
                }
            }
        });
    }

    private function calculateMinutes($currentActivity)
    {
        if (!$currentActivity->start || !$currentActivity->end) {
            return 0;
        }

        return $currentActivity->start->diffInSeconds($currentActivity->end) / 60;
    }

    private function buildScheduleStructure()
    {
        $schedule = collect();
        $st = $this->startTime->copy();

        while ($st->lte($this->endTime)) {
            $schedule->push(collect([
                'time' => $st->copy()
            ]));

            $st->addMinute(1);
        }

        return $schedule;
    }

    private function generateTracker()
    {
        if (Setting::getSetting('generate_tracker', 0)) {
            return;
        }

        $firstActivityMinutes = 0;
        if ($this->activities->get('first')) {
            $firstActivityMinutes = $this->activities
                ->get('first')
                ->reduce(function ($carry, $item) {
                    return $carry + $item->minutes;
                }, 0);
        }

        $lastActivityMinutes = 0;
        if ($this->activities->get('last')) {
            $lastActivityMinutes = $this->activities
                ->get('last')
                ->reduce(function ($carry, $item) {
                    return $carry + $item->minutes;
                }, 0);
        }

        $trackerStartTime = $this->startTime->copy();
        $trackerEndTime = $this->endTime->copy();

        if (Setting::getSetting('start_tracker_after_first_activities', 1)) {
            $trackerStartTime->addMinutes($firstActivityMinutes);
        }

        if (Setting::getSetting('end_tracker_before_last_activities', 1)) {
            $trackerEndTime->subMinutes($lastActivityMinutes);
        }

        $trackerTypes = Tracker::getTrackers();
        $tracker = collect();

        $trackerTypes->each(function ($trackerType) use ($tracker, $trackerStartTime, $trackerEndTime) {
            $activity = Activity::factory()->make([
                'name' => Str::ucfirst($trackerType->name),
                'sound' => $trackerType->sound ?? null
            ]);

            switch ($trackerType->type) {
                case "divide":
                    $diffInMs = $trackerStartTime->diffInSeconds($trackerEndTime) * 1000;
                    for ($i = 0; $i < 2500 / 500; $i++) {
                        $middleGap = $i !== 0 ? $diffInMs / $i : 0;

                        $tracker->push(collect([
                            'time' => $trackerStartTime->copy()->add($middleGap, 'milliseconds'),
                            'activity' => $activity
                        ]));
                    }
                    break;
                case 'max':
                    $diffInMs = $trackerStartTime->diffInSeconds($trackerEndTime) * 1000;
                    for ($i = 0; $i < $trackerType->max; $i++) {
                        $middleGap = $i !== 0 ? $diffInMs / $i : 0;

                        $tracker->push(collect([
                            'time' => $trackerStartTime->copy()->add($middleGap, 'milliseconds'),
                            'activity' => $activity
                        ]));
                    }
                    break;
                case 'increment':
                    $st = $trackerStartTime->copy();
                    $et = $trackerEndTime->copy();

                    while ($st->lte($et)) {
                        $tracker->push(collect([
                            'time' => $st->copy(),
                            'activity' => $activity
                        ]));

                        $st->addHour($trackerType->increment);
                    }
                    break;
            }
        });

        return $tracker->sortBy('time');
    }

    private function generateDate($time = null)
    {
        $now = Carbon::now();

        if (!$time) {
            return $now;
        }

        $now->setTimeFromTimeString($time);

        if ($this->startTime && $now->lt($this->startTime)) {
            $now->addDay(1);
        }

        return $now;
    }
}
