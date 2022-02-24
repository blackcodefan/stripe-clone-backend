<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
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
            'result' => \App\Http\Resources\Status::collection(\App\Models\Status::all())
        ]);

    }

}