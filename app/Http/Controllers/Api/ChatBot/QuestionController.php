<?php

namespace App\Http\Controllers\Api\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\ChatBot\QuestionBot;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function getall(){
        return response()->json(QuestionBot::with('options')->orderBy('id', 'asc')->get());
    }
    public function index()
    {
        return response()->json(QuestionBot::with('options')->orderBy('id', 'asc')->paginate(100));
    }

    public function show($id)
    {
        return response()->json(QuestionBot::with('options.nextQuestion')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:text,button',
            'is_start' => 'boolean',
            'active' => 'boolean',
        ]);

        if (isset($validated['is_start']) && $validated['is_start']) {
            $exists = QuestionBot::where('is_start', true)->exists();
            if ($exists) {
                return response()->json(['message' => 'Já existe uma pergunta inicial.'], 422);
            }
        }

        $question = QuestionBot::create($validated);

        return response()->json($question, 201);
    }

    public function update(Request $request, $id)
    {
        $question = QuestionBot::findOrFail($id);

        $validated = $request->validate([
            'text' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:text,button',
            'is_start' => 'sometimes|boolean',
            'active' => 'sometimes|boolean',
        ]);

        $question->update($validated);

        return response()->json($question);
    }

    public function destroy($id)
    {
        QuestionBot::destroy($id);
        return response()->json(['success' => true]);
    }
}
