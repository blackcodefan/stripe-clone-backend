<?php

namespace App\Models;

use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerObject extends Model
{
    use HasFactory, GeneratesUuid, SoftDeletes, LogsActivity;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'customer_id',
        'object_id',
        'license_plate',
        'spot',
        'object_type_id',
        'brand',
        'type',
        'width',
        'length',
        'chassis',
        'status',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CustomerObject);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class)->withTrashed();
    }

    public function appointments()
    {
        return $this->hasMany(\App\Models\Appointment::class)->orderBy('appointment_at', 'DESC');
    }

    public function object_type()
    {
        return $this->belongsTo(\App\Models\ObjectType::class);
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Note::class, 'model');
    }

    public function setLicensePlateAttribute($value)
    {
        $this->attributes['license_plate'] = strtoupper($value);
    }

    public function setSpotAttribute($value)
    {
        $this->attributes['spot'] = strtoupper($value);
    }

    public function getStatusAttribute($value)
    {
        switch ($value) {
            case 1:
                return __('global.is_out');
                break;
            case 2:
                return __('global.is_in');
                break;
            default:
                return __('global.unknown');

        }
    }

}
