<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\DB;

class SubmitExamController extends Controller
{
    public function createSubmitExam(Request $request)
    {
        $exam_id = $request->exam_id;
        $user_id = $request->user_id;
        $user_answer = $request->user_answers;
     
        // Initialize an empty array to store user submit exam data
        $userSubmit = [];
    
        // Start a database transaction
        DB::beginTransaction();
    
        try {
            // Loop through user answers and create UserAnswer records
            foreach ($user_answer as $answer) {
                $submitExam = UserAnswer::create([
                    'exam_id' => $exam_id,
                    'user_id' => $user_id,
                    'question_id' => $answer['question_id'],
                    'choice_id' => $answer['choice_id'],
                ]);
                
                // Add the created UserAnswer instance to the userSubmit array
                $userSubmit[] = $submitExam;
            }
    
            // Commit the transaction
            DB::commit();
            
            // Return a success response with the created user answers
            return response()->json($userSubmit, 201);
        } catch (\Exception $e) {
            // If an error occurs, rollback the transaction
            DB::rollBack();
            
            // Return an error response
            return response()->json(['error' => 'Failed to submit exam'], 500);
        }
    }
}
