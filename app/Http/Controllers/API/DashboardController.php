<?php

namespace App\Http\Controllers\API;

use App\Models\Exam;
use App\Models\User;
use App\Models\Course;
use App\Models\Question;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function getDashboardDetail (Request $request) {
        $courseCount = Course::count();
        $examCount = Exam::count();
        $questionCount = Question::count();
        $lastThreeExams = $request->user()->exams()->with('questions','course')->get();

        $obj = [
            'courseCount' => $courseCount,
            'examCount' => $examCount,
            'questionCount' => $questionCount,
            'exam' => $lastThreeExams
        ];
        return response()->json($obj, 200);
    }

    public function getUserDashboardDetail (Request $request , $id) {
        $userExamList = DB::table('exams as e')
        ->select('e.exam_title','e.id', 'e.created_at' ,'c.course_title', 'e.exam_duration', 'u.id as user_id')
        ->join('courses as c', 'c.id', '=', 'e.course_id')
        ->join('enrollments as en', 'en.course_id', '=', 'e.course_id')
        ->join('users as u', 'u.id', '=', 'en.user_id')
        ->where('u.id', $id)
        ->orderby('created_at', 'desc')
        ->limit(6)
        ->get();

        $user = User::findOrFail($id);
        $enrollments = Enrollment::with('user','course','course.user')->where('user_id',$id)->get();

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
        ->where('ua.user_id', $id)
        ->groupBy('e.id', 'e.exam_title', 'e.created_at', 'c.id', 'c.course_title', 'ua.user_id')
        ->orderby('created_at','desc')
        ->get();

        return response()->json([
            'userExamList' => $userExamList,
            'enrollments' => $enrollments,
            'userResults' => $userResults
        ], 200);
    }
}
