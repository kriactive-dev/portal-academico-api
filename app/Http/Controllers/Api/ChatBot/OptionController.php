<?php

namespace App\Http\Controllers\Api\ChatBot;

use App\Http\Controllers\Controller;
use App\Models\ChatBot\OptionBot;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    public function index()
    {
        return response()->json(OptionBot::with('question', 'nextQuestion')->orderBy('id', 'asc')->get());
    }

    public function indexByQuestion($questionId)
    {
        return response()->json(OptionBot::where('question_id', $questionId)->with('nextQuestion')->orderBy('id', 'asc')->get());
    }

    public function show($id)
    {
        return response()->json(OptionBot::with('question', 'nextQuestion')->findOrFail($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question_bot_id' => 'required',
            'label' => 'required|string',
            'value' => 'required|string',
            'next_question_bot_id' => 'nullable',
        ]);


        $option = OptionBot::create($validated);

        return response()->json($option, 201);
    }

    public function update(Request $request, $id)
    {
        $option = OptionBot::findOrFail($id);

        $validated = $request->validate([
            'question_bot_id' => 'sometimes',
            'label' => 'sometimes|required|string',
            'value' => 'sometimes|required|string',
            'next_question_bot_id' => 'nullable',
        ]);

        $option->update($validated);

        return response()->json($option);
    }

    public function destroy($id)
    {
        OptionBot::destroy($id);
        return response()->json(['success' => true]);
    }
}
