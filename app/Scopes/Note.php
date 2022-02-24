<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Note implements Scope
{

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
//        if(\Auth::check()) {
//            // trough customer relation (object -> customer -> company)
//            $builder->whereHas('customer', function ($q) {
//                $q->where('company_id', \Auth::user()->company->id);
//            });
//        }
    }

}