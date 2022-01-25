<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marital extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 'maritals';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
