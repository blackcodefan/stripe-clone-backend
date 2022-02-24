<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use HasFactory, GeneratesUuid, LogsActivity;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function locations()
    {
        return $this->hasMany(\App\Models\Location::class);
    }

    public function appointments()
    {
        return $this->hasMany(\App\Models\Appointment::class);
    }

    public function customers()
    {
        return $this->hasMany(\App\Models\Customer::class);
    }

}
