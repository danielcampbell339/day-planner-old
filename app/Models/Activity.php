<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory, Uuids;

    protected $with = [
        'children',
        'type',
        'frequency',
        'commands'
    ];

    protected $casts = [
        'days' => 'array'
    ];

    protected $guarded = [];

    public function children()
    {
        return $this
            ->hasMany(self::class, 'parent_activity_id')
            ->with('children');
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function frequency()
    {
        return $this->belongsTo(ActivityFrequency::class, 'id', 'activity_id');
    }

    public function commands()
    {
        return $this->belongsToMany(Command::class, ActivityCommand::class);
    }

    public function getStartAttribute($value)
    {
        return Carbon::now()->createFromTimeString($value);
    }

    public function getEndAttribute($value)
    {
        return Carbon::now()->createFromTimeString($value);
    }

    public function scopeGetActivities()
    {
        // Get the inital list of activities
        $collection = $this
            ->where('disabled', 0)
            ->whereNull('parent_activity_id')
            ->doesntHave('children')
            ->get();

        $extraType = Type::where('name', 'extra')->first();

        // Get the activities that are list activities and shuffle them
        $listActivities = $this->getListActivities();

        // Yeet all the other activities into extra aside from the first
        foreach ($listActivities as $parents) {
            $parents->each(function ($activity, $key) use ($extraType) {
                if ($key != 0) {
                    $activity->relations['type'] = $extraType;
                }
            });
        }

        $collection = $collection->concat($listActivities->flatten());

        // Remove any items that aren't for today
        $collection = $collection->filter(function ($activity) {
            if (!$activity->days) {
                return true;
            }

            return in_array(Carbon::now()->format('l'), $activity->days);
        });

        // Remove dates
        $collection = $collection->filter(function ($activity) {
            if (!$activity->date) {
                return true;
            }

            return $activity->date == Carbon::now()->format('Y-m-d');
        });

        // Remove any frequency activities that have finished
        $collection = $collection->filter(function ($activity) {
            if (!$activity->frequency) {
                return true;
            }

            return !$activity->frequency->date_completed;
        });

        $collection = $collection->groupBy('type.name');

        $allTypes = Type::all();
        $allTypes->each(function ($type) use ($collection) {
            if ($type->shuffleable && $collection->get($type->name)) {
                $collection->put(
                    $type->name,
                    $collection->get($type->name)->shuffle()
                );
            }

            if ($type->reversable && $collection->get($type->name)) {
                $collection->put(
                    $type->name,
                    $collection->get($type->name)->reverse()
                );
            }

            if ($type->sortable && $collection->get($type->name)) {
                $collection->put(
                    $type->name,
                    $collection->get($type->name)->sortByDesc(function ($activity) {
                        return
                            $activity->days &&
                            in_array(Carbon::now()->format('l'), $activity->days) &&
                            count($activity->days) == 1;
                    })
                );

                $collection->put(
                    $type->name,
                    $collection->get($type->name)->sortByDesc(function ($activity) {
                        return
                            $activity->priority &&
                            $activity->priority == 'high';
                    })
                );
            }
        });

        // Reverse the last activities array so it can be easier to parse
        if ($collection->get('last')) {
            $collection->put('last', $collection->get('last')->reverse());
        }

        // Reverse the time activities array so it can be easier to parse
        if ($collection->get('time')) {
            $collection->put('time', $collection->get('time')->reverse());
        }

        if (Setting::getSetting('skip_first', 1)) {
            $collection->put('first', collect([]));
        }

        if (Setting::getSetting('skip_last', 1)) {
            $collection->put('last', collect([]));
        }

        return $collection;
    }

    public function scopeGetListActivities()
    {
        return $this
            ->where('disabled', 0)
            ->whereNotNull('parent_activity_id')
            ->doesntHave('children')
            ->get()
            ->shuffle()
            ->groupBy('parent_activity_id');
    }
}
