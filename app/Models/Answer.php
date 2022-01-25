<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Answer extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'answers';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
