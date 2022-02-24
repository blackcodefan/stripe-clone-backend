<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PasswordController extends Controller
{

    public function update(\App\Http\Requests\PasswordRequest $request)
    {

        $user = \Auth::user();
        $user->password = bcrypt($request->get('password'));
        $user->save();

        return response()->json([
            'status' => 'true'
        ]);

    }

}