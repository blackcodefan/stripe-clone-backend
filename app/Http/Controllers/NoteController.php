<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $model_id = $request->query('model_id');
        $model_type = $request->query('model_type');

        $notes = Note::where('model_id', $model_id)
            ->where('model_type', $model_type)
            ->with('user')->orderby('created_at', 'desc')
            ->paginate(5);

        return response()->json([
            'status' => 'true',
            'result' => $notes,
        ]);
    }

    public function store(\App\Http\Requests\NoteRequest $request)
    {
        Note::create([
            'note' => $request->note,
            'model_type' => $request->model_type,
            'model_id' => $request->model_id,
            'user_id' => \Auth::user()->id,
        ]);

        return response()->json([
            'status' => 'true',
        ]);
    }

    public function update(\App\Http\Requests\NoteRequest $request, \App\Models\Note $note)
    {
        $note->update($request->except(['user', 'created_at', 'updated_at', 'deleted_at']));
        return response()->json([
            'status' => 'true',
        ]);
    }

    public function destroy(\App\Models\Note $note)
    {
        $note->delete();

        return response()->json([
            'status' => 'true',
        ]);
    }
}
