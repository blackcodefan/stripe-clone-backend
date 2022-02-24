<?php

namespace App\Models;

use Carbon;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Note extends Model
{
    use HasFactory, SoftDeletes, GeneratesUuid, LogsActivity;

    protected static $logFillable = true;
    protected static $logOnlyDirty = true;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'note',
        'model_type',
        'model_id',
        'user_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\Note);
    }

    public function model()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'user_id');
    }

    public function getCreatedAtAttribute($value)
    {
        $c = new Carbon\Carbon($value);

        return $c->diffForHumans();
    }
}
