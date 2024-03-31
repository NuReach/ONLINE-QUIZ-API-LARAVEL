<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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
        $questions = Question::with('choices')->get();
        return response()->json($questions);
    }

    public function createQuestion(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question_prompt' => 'required',
            'question_type' => 'required',
            'question_level' => 'required',
            'question_answer' => 'required',
            'question_choices' => 'required|array', // Ensure question_choices is an array
            'question_choices.*.text' => 'required', // Validate each choice text
        ]);
    
        // If validation fails, return a 400 response with validation errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Create the question
            $question = Question::create([
                'question_prompt' => $request->input('question_prompt'),
                'question_image' => $request->input('question_image'),
                'question_type' => $request->input('question_type'),
                'question_level' => $request->input('question_level'),
                'question_answer' => $request->input('question_answer'),
            ]);
    
            // Create choices for the question
            $createdChoices = [];
            foreach ($request->question_choices as $choiceData) {
                $choice = Choice::create([
                    'question_id' => $question->id,
                    'text' => $choiceData['text']
                ]);
                $createdChoices[] = $choice;
            }
    
            // Commit the transaction
            DB::commit();
    
            // Return a success response with the created question and choices
            return response()->json(['message' => 'Question and choices created successfully', 'question' => $question, 'choices' => $createdChoices], 201);
        } catch (\Exception $e) {
            // If an error occurs, rollback the transaction
            DB::rollback();
            
            // Return an error response
            return response()->json(['error' => 'Failed to create question and choices'], 500);
        }
    }
  
    public function updateQuestion(Request $request, $id)
        {
            // Find the question
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
                'choices.*.text' => 'required', // Validation rule for choice text
            ]);

            // If validation fails, return a 400 response with validation errors
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            // Start a database transaction
            DB::beginTransaction();

            try {
                // Update the question with the new data
                $question->update([
                    'question_prompt' => $request->input('question_prompt'),
                    'question_image' => $request->input('question_image'),
                    'question_type' => $request->input('question_type'),
                    'question_level' => $request->input('question_level'),
                    'question_answer' => $request->input('question_answer'),
                ]);

                $updatedChoices = [];
                // Update or create choices
                foreach ($request->choices as $choiceData) {
                    $choice = Choice::updateOrCreate(
                        ['id' => $choiceData['id']], 
                        [
                            'question_id' => $id,
                            'text' => $choiceData['text'],
                        ]
                    );
                    $updatedChoices[] = $choice;
                }

                // Commit the transaction
                DB::commit();

                // Return a success response
                return response()->json(['message' => 'Question and choices updated successfully', 'question' => $question , 'choices' => $updatedChoices]);
            } catch (\Exception $e) {
                // If an error occurs, rollback the transaction
                DB::rollback();
                
                // Return an error response
                return response()->json(['error' => 'Failed to update question and choices'], 500);
            }
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
