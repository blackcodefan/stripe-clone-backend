<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Prospect extends Model
{
    use HasFactory, GeneratesUuid, SoftDeletes, LogsActivity;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'user_id',
        'initials',
        'firstname',
        'lastname',
        'email',
        'phone',
        'street',
        'number',
        'zipcode',
        'city',
        'brand',
        'type',
        'license_plate',
        'width',
        'length',
        'note',
        'object_type_id',
        'delivery_at',
        'status_id',
        'object_id'
    ];

    protected $dates = [
        'delivery_at'
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\Prospect);
    }

    public function getFullNameAttribute()
    {
        return implode(' ', [
            $this->firstname,
            $this->lastname,
        ]);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function object_type()
    {
        return $this->belongsTo(\App\Models\ObjectType::class);
    }

    public function status()
    {
        return $this->hasOne(\App\Models\Status::class, 'id', 'status_id');
    }
    public function object()
    {
        return $this->hasOne(\App\Models\CustomerObject::class, 'id', 'object_id');
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Note::class, 'model')->latest();
    }

}
