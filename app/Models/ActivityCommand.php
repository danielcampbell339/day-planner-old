<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActivityCommand extends Pivot
{
    use Uuids;
}
