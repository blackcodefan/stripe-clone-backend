<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IframeController extends Controller
{

    public function show($uuid)
    {

        $company = \App\Models\Company::whereUuid($uuid)->first();

        if(!$company) {
            return response()->json(['message' => 'Not Found!'], 404);
        }

        return response()->json([
            'status' => 'true',
            'result' => new \App\Http\Resources\Company($company)
        ]);

    }

}