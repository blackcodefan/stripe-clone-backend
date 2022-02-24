<?php

namespace App\Models;

use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Laravel\Cashier\Billable;
use function Illuminate\Events\queueable;

class Customer extends Model
{
    use HasFactory, GeneratesUuid, SoftDeletes, LogsActivity;
    use Billable;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $guarded = [];

    protected $fillable = [
        'company_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'phone2',
        'street',
        'number',
        'zipcode',
        'city',
        'customer_number',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\Customer);

        static::updated(queueable(function ($customer) {
            $customer->syncStripeCustomerDetails();
        }));
    }

    public function stripeName()
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function objects()
    {
        return $this->hasMany(\App\Models\CustomerObject::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    public function getFullNameAttribute()
    {
        return implode(' ', [
            $this->firstname,
            $this->lastname,
        ]);
    }

    public function notes()
    {
        return $this->morphMany(\App\Models\Note::class, 'model');
    }
}
