<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionType extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'question_types';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
