<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $responseCode = 200;

        $cache = Cache::get($request->cache_key);
        if (!$cache) {
            $responseCode = 204;
        }

        return response($cache, $responseCode);
    }
}
