<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserAnswer;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{

    public function getResult (Request $request) {
        $exams = $request->user()->exams()
        ->with('course')
        ->where('status','!=','prepared')
        ->get();
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

            $userAnswers = UserAnswer::with('user','choice','exam','exam.course')
            ->where('exam_id', $id)
            ->where('user_id', $userId)
            ->get();


            $groupedData[] = $userAnswers  ;
        }

        $deconstructedObjects = [];

        for ($i = 0; $i < sizeof($groupedData); $i++) {
            $totalCorrect = 0;
            for ($j = 0; $j < sizeof($groupedData[$i]); $j++) {
                if ($groupedData[$i][$j]['choice']['is_correct'] == 1) {
                    $totalCorrect++;
                }
            }
        
            $percentageScore = $totalCorrect / sizeof($questions) * 100;
        
            $grade;
            if ($percentageScore >= 90) {
                $grade = 'A';
            } elseif ($percentageScore >= 80) {
                $grade = 'B';
            } elseif ($percentageScore >= 70) {
                $grade = 'C';
            } elseif ($percentageScore >= 60) {
                $grade = 'D';
            } else {
                $grade = 'E';
            }
        
            $obj = [
                'exam' => $groupedData[$i][0]['exam'],
                'course' => $groupedData[$i][0]['exam']['course'],
                'user' => $groupedData[$i][0]['user'],
                'correct' => $totalCorrect,
                'state' => sizeof($questions),
                'created_at' => $groupedData[$i][0]['exam']['created_at'],
                'score' => $percentageScore,
                'grade' => $grade, // Add 'grade' to the object
            ];
        
            $deconstructedObjects[] = $obj;
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

    public function getUserResultList ( $user_id ) {
        $userResults = DB::table('user_answers as ua')
        ->select(
            'e.id as exam_id',
            'e.exam_title',
            'e.created_at',
            'c.id as course_id',
            'c.course_title',
            'ua.user_id',
            DB::raw('COUNT(CASE WHEN ch.is_correct = 1 THEN 1 ELSE NULL END) AS correct_choices_count'),
            DB::raw('COUNT(*) AS total_choices_count')
        )
        ->join('exams as e', 'e.id', '=', 'ua.exam_id')
        ->join('courses as c', 'c.id', '=', 'e.course_id')
        ->join('choices as ch', 'ch.id', '=', 'ua.choice_id')
        ->where('ua.user_id', $user_id)
        ->groupBy('e.id', 'e.exam_title', 'e.created_at', 'c.id', 'c.course_title', 'ua.user_id')
        ->orderby('created_at','desc')
        ->get();

        return response()->json($userResults, 200);
    }
}
