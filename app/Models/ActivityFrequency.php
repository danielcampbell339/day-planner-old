<?php

namespace App\Models;

use Exception;
use Carbon\Carbon;
use App\Traits\Uuids;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActivityFrequency extends Pivot
{
    use Uuids;

    protected $with = [
        'frequency'
    ];

    protected $appends = [
        'renewal_date'
    ];

    protected $casts = [
        'date_completed' => 'date'
    ];

    public function frequency()
    {
        return $this->belongsTo(Frequency::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function getRenewalDateAttribute()
    {
        if (!$this->date_completed) {
            return null;
        }

        return $this->date_completed->add($this->amount, $this->frequency->name);
    }

    public function scopeUpdateExpiredFrequencyActivities()
    {
        $res = $this
            ->with('activity')
            ->whereNotNull('date_completed')
            ->get();

        $res->each(function ($activityFrequency) {
            if ($activityFrequency->renewal_date->lte(Carbon::now())) {
                $activityFrequency->update([
                    'date_completed' => null
                ]);
            }
        });
    }
}
