<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory, Uuids;

    public function scopeGetSetting($query, $name, $value)
    {
        return $this->where(['name' => $name, 'value' => $value])->exists();
    }
}
