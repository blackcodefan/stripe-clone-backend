<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ObjectTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return response()->json([
            'status' => 'true',
            'result' => \App\Http\Resources\ObjectType::collection(\App\Models\ObjectType::all())
        ]);

    }

}