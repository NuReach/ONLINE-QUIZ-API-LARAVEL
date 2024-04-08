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
        $question = Question::with('choices')->find($id);
        if ($question) {
            return response()->json($question);
        } else {
            return response()->json(['error' => 'Question not found'], 404);
        }
    }
    public function searchQuestions ( Request $request , $search , $sortBy , $sortDir ) {
        $page = 6;
        if ($search == "all") {
            $questions = $request->user()->questions()
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);
        }else{
            $questions = $request->user()->questions()
            ->where(
             function($query) use ($search) {
                 $query->where('question_prompt','LIKE',"%$search%")
                 ->orWhere('question_type','LIKE',"%$search%")
                 ->orWhere('question_level','LIKE',"%$search%");
             }
            )
            ->orderBy($sortBy, $sortDir)
            ->paginate($page);

        }
        return response()->json($questions, 200);
    }


    public function getAllQuestion(Request $request)
    {
        $questions = $request->user()->questions()->get();
        return response()->json($questions);
    }

    public function createQuestion(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'question_prompt' => 'required',
            'question_type' => 'required',
            'question_level' => 'required',
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
                'mark' => $request->input('question_mark'),
                'author' => $request->input('author')
            ]);
    
            // Create choices for the question
            $createdChoices = [];
            foreach ($request->question_choices as $choiceData) {
                $choice = Choice::create([
                    'question_id' => $question->id,
                    'text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct']
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
            $question = Question::find($id);

            if (!$question) {
                return response()->json(['error' => 'Question not found'], 404);
            }

            $validator = Validator::make($request->all(), [
                'question_prompt' => 'required',
                'question_type' => 'required',
                'question_level' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            DB::beginTransaction();

            try {
                $question->update([
                    'question_prompt' => $request->input('question_prompt'),
                    'question_image' => $request->input('question_image'),
                    'question_type' => $request->input('question_type'),
                    'question_level' => $request->input('question_level'),
                    'mark' => $request->input('question_mark'),
                    'author' =>$request->input('author')
                ]);

                $updatedChoices = [];
                foreach ($request->input('question_choices') as $choiceData) {
                    $choice = Choice::updateOrCreate(
                        ['id' => $choiceData['id']], 
                        [
                            'question_id' => $id,
                            'text' => $choiceData['text'],
                            'is_correct' => $choiceData['is_correct']
                        ]
                    );
                    $updatedChoices[] = $choice;
                }

                DB::commit();

                return response()->json(['message' => 'Question and choices updated successfully', 'question' => $question , 'choices' => $updatedChoices]);
            } catch (\Exception $e) {
                DB::rollback();
    
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
