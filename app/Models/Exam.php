<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Question;

class Exam extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function questions ()
    {
        return $this->belongsToMany(Question::class, 'exam_question');
    }
}
