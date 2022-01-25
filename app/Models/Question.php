<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'questions';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function options()
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'question_id');
    }

    public function question_type()
    {
        return $this->belongsTo(QuestionType::class);
    }
}
