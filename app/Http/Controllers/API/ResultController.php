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
}
