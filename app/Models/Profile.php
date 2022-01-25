<?php

namespace App\Models;

use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $guarded = [];
    protected $table = 'profiles';
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'spouse_check',
        'question_position',
        'agree',
        'picture',
        'picture_blur',
        'url_picture',
        'url_picture_blur',
        'credit_file',
        'obgyn_file'
    ];
    protected $appends = ['url_picture','url_picture_blur','report_url','obgyn_url','report_check','obgyn_check'];
    protected $attributes = [
        'membership_id' => 1
    ];

    public function getUrlPictureAttribute($value)
    {
        return $this->picture ? URL::to('/') . Storage::url($this->picture) : null;
    }

    public function getUrlPictureBlurAttribute($value)
    {
        return $this->picture_blur ? URL::to('/') . Storage::url($this->picture_blur) : null;
    }

    public function getReportUrlAttribute($value)
    {
        return $this->credit_file ? URL::to('/') . Storage::url($this->credit_file) : null;
    }

    public function getObgynUrlAttribute($value)
    {
        return $this->obgyn_file ? URL::to('/') . Storage::url($this->obgyn_file) : null;
    }

    public function getReportCheckAttribute($value)
    {
        return $this->credit_file ? true : false;
    }

    public function getObgynCheckAttribute($value)
    {
        return $this->obgyn_file ? true : false;
    }

    public function showPicture($cond){
        if($cond == 0){
            $this->attributes['picture_url'] = $this->url_picture_blur;
        }else{
            $this->attributes['picture_url'] = $this->url_picture;
        }
    }

    public function near($state_auth){
        $this->attributes['near'] = (strtolower($this->state) == strtolower($state_auth)) ? true : false;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'profile_id', 'id');
    }

    /*
    public function getAgeAttribute($value){
        if(isset($this->users[0])){
            $age = Carbon::parse($this->users[0]->date_birth)->age;
            //unset($this->users);
            return $age;
        }else{
            return null;
        }
    }*/

    public function answers()
    {
        return $this->hasMany(Answer::class, 'profile_id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membership_id', 'id');
    }

    public function scopeManyTimesSurrogate($query){
        return $query->with(['answers' => function($q){
            $q->where('question_id',23);
        },
        'answers.option'
        ]);
    }
}
