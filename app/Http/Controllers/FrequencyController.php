<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityFrequency;
use Illuminate\Support\Facades\Cache;

class FrequencyController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Update the field
        ActivityFrequency::find($request->id)->update([
            'date_completed' => now()->timestamp
        ]);

        // Remove the frequency activity!
        $schedule = $request->schedule;
        foreach ($schedule as $key => $sch) {
            if (empty($sch['activity']['frequency'])) {
                continue;
            }

            if ($sch['activity']['frequency'] == $request->id) {
                $schedule[$key]['activity'] = [
                    'name' => 'Free Time'
                ];
            }
        }

        $response = response([
            'schedule' => $schedule,
            'cache_key' => $request->cache_key
        ]);

        // Now update the cache
        Cache::forget($request->cache_key);
        Cache::put($request->cache_key, json_encode($response->original));

        return $response;
    }
}
