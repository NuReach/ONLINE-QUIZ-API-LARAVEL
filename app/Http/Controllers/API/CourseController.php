<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function getOneCourse($id)
    {
        $course = Course::find($id);
        if ($course) {
            return response()->json($course);
        } else {
            return response()->json(['error' => 'Course not found'], 404);
        }
    }

    public function getAllCourseBelongToUser(Request $request)
    {
        $courses = $request->user()->courses()->paginate(4); 
        return response()->json($courses, 200);
    }

    public function getAllCourse()
    {
        $courses = Course::all();
        return response()->json($courses);
    }

    public function createCourse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_code' => 'required|unique:courses',
            'course_title' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $course = Course::create([
            'course_code' => $request->input('course_code'),
            'course_title' => $request->input('course_title'),
            'author'=> $request->input('author')
        ]);

        return response()->json($course, 201);
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'course_code' => 'required|unique:courses,course_code,' . $id,
            'course_title' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $course->update([
            'course_code' => $request->input('course_code'),
            'course_title' => $request->input('course_title'),
        ]);

        return response()->json($course);
    }

    public function deleteCourse($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json(['error' => 'Course not found'], 404);
        }

        $course->delete();
        return response()->json(['message'=>"Delete Course  Successfully"]);
    }
}