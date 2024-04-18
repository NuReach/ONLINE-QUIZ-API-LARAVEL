<?php

namespace App\Http\Controllers\API;

use App\Models\Exam;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class DashboardController extends Controller
{
    public function getDashboardDetail (Request $request) {
        $courseCount = Course::count();
        $examCount = Exam::count();
        $questionCount = Question::count();
        $lastThreeExams = $request->user()->exams()->with('questions')->get();

        $obj = [
            'courseCount' => $courseCount,
            'examCount' => $examCount,
            'questionCount' => $questionCount,
            'exam' => $lastThreeExams
        ];
        return response()->json($obj, 200);
    }
}
