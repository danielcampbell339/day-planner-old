<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracker extends Model
{
    use HasFactory, Uuids;

    public function scopeGetTrackers()
    {
        return $this
            ->where('disabled', 0)
            ->get();
    }
}
