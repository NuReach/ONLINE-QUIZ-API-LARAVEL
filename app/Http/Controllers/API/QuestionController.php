<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function getOneQuestion($id)
    {
        $question = Question::find($id);
        if ($question) {
            return response()->json($question);
        } else {
            return response()->json(['error' => 'Question not found'], 404);
        }
    }


    public function getAllQuestion()
    {
        $questions = Question::all();
        return response()->json($questions);
    }

    public function createQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_prompt' => 'required',
            'question_type' => 'required',
            'question_level' => 'required',
            'question_answer' => 'required', // Add validation rules for question_answer
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $question = Question::create([
            'question_prompt' => $request->input('question_prompt'),
            'question_image' => $request->input('question_image'),
            'question_type' => $request->input('question_type'),
            'question_level' => $request->input('question_level'),
            'question_answer' => $request->input('question_answer'),
        ]);

        return response()->json($question, 201);
    }
    public function updateQuestion(Request $request, $id)
    {
        // Find the question by ID
        $question = Question::find($id);

        // If the question doesn't exist, return a 404 response
        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question_prompt' => 'required',
            'question_type' => 'required',
            'question_level' => 'required',
            'question_answer' => 'required',
        ]);

        // If validation fails, return a 400 response with validation errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Update the question with the new data
        $question->update([
            'question_prompt' => $request->input('question_prompt'),
            'question_image' => $request->input('question_image'),
            'question_type' => $request->input('question_type'),
            'question_level' => $request->input('question_level'),
            'question_answer' => $request->input('question_answer'),
        ]);

        // Return the updated question
        return response()->json($question);
    }

    public function deleteQuestion($id)
    {
        // Find the question by ID
        $question = Question::find($id);

        // If the question doesn't exist, return a 404 response
        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Delete the question
        $question->delete();

        // Return a success message
        return response()->json(['message' => 'Question deleted successfully'], 200);
    }


}
