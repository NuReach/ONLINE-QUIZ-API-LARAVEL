<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAnswer;
use App\Models\Exam;

class ResultController extends Controller
{
    public function getResult () {

        $distinctExamIds = UserAnswer::distinct()->pluck('exam_id');
        $exams = Exam::whereIn('id', $distinctExamIds)->get();
        return response()->json($exams, 200);

    }

    public function getResultStudentScore ( $id ) {

        $exams = Exam::find($id);
        $questions = $exams->questions;


        $groupedUserAnswers = UserAnswer::select('user_id') 
        ->where('exam_id', $id)
        ->groupBy('user_id')
        ->get();

        $groupedData = [];

        foreach ($groupedUserAnswers as $userAnswer) {

            $userId = $userAnswer->user_id;

            $userAnswers = UserAnswer::with('user','choice','exam')
            ->where('exam_id', $id)
            ->where('user_id', $userId)
            ->get();


            $groupedData[] = $userAnswers  ;
        }

        $deconstructedObjects = [];

        for ($i=0; $i <sizeof($groupedData) ; $i++) { 
            $totalCorrect = 0;
            for ($j=0; $j < sizeof($groupedData[$i]) ; $j++) { 
                if ($groupedData[$i][$j]['choice']['is_correct'] == 1) {
                    $totalCorrect ++ ;
                }
            }
            $obj = [
                'exam' => $groupedData[$i][0]['exam']['exam_title'],
                'user' => $groupedData[$i][0]['user']['name'],
                'correct' => $totalCorrect,
                'state' => sizeof($questions),
            ];
            $deconstructedObjects [] = $obj;
        }


        return response()->json($deconstructedObjects, 200);
    }

    public function getUserResult (Request $request ,$user_id,$exam_id) {
        $userResult = UserAnswer::with('exam','exam.course','question','question.choices','choice','user')
        ->where('user_id', $user_id)
        ->where('exam_id', $exam_id)
        ->get()
        ->unique('question_id')
        ->sortBy(function ($item) {
            return $item->question->question_prompt;
        });
    

        $answers = [];
        foreach ($userResult as $key => $item) {
            $answers[] = [
                'question' => $item['question'],
                'choice' => $item['choice'],
            ];
        }

        $correct_choices_count = 0;
        foreach ($answers as $item) {
            if ($item['choice']['is_correct'] == 1) {
                $correct_choices_count++;
            }
        }
        



        $userResultObj = [
            'exam' => $userResult[0]['exam'],
            'user' => $userResult[0]['user'],
            'answers' => $answers,
            'total_questions'=>sizeof($userResult[0]['exam']['questions']),
            'total_is_correct'=> $correct_choices_count,
            'total_missing' => sizeof($userResult[0]['exam']['questions'])-sizeof($answers)
        ];

       //$exam = Exam::with('questions','questions.choices')->where('id',$exam_id)->get();


        return response()->json($userResultObj, 200);
    }
}
