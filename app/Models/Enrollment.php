<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enrollment extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user ()
    {
        return $this->belongsTo(User::class , "user_id" , "id");
    }
    public function course ()
    {
        return $this->belongsTo(Course::class , "course_id" , "id");
    }
}
