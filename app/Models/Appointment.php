<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Appointment extends Model
{
    use HasFactory, GeneratesUuid, SoftDeletes, LogsActivity;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $dates = [
        'appointment_at'
    ];

    protected $fillable = [
        'user_id',
        'location_id',
        'status_id',
        'customer_object_id',
        'name',
        'email',
        'phone',
        'note',
        'appointment_at',
        'type'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\Appointment);
    }

    public function object()
    {
        return $this->hasOne(\App\Models\CustomerObject::class, 'id', 'customer_object_id')->withTrashed();
    }

    public function status()
    {
        return $this->hasOne(\App\Models\Status::class, 'id', 'status_id');
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Note::class, 'model')->latest();
    }

}
