<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Membership extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'memberships';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function profiles()
    {
        return $this->hasMany(Profile::class, 'membership_id', 'id');
    }
}
