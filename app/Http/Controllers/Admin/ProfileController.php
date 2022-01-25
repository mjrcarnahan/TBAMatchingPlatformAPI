<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use App\Models\Question;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function indexIntendents(Request $request){

        $profiles =  Profile::with(['users' => function ($q) {
            $q->orderBy('id','ASC');
        }, 'users.sex'])
        ->whereHas('users')
        ->where('type_id', 1)->get();

        $response = $profiles->map(function ($profile) {

            $full_name = "";
            $age = "";
            $sex = "";
            $spouse = "";
            $spouse_age = "";
            $spouse_sex = "";
            $status = "";

            if(isset($profile->users[0])){
                $full_name = $profile->users[0]->first_name;
                if(!empty($profile->users[0]->middle_name)){
                    $full_name .= " ".$profile->users[0]->middle_name;
                }
                $full_name .= " ".$profile->users[0]->last_name;
                if(!empty($profile->users[0]->maiden_name)){
                    $full_name .= " ".$profile->users[0]->maiden_name;
                }

                $age = Carbon::parse($profile->users[0]->date_birth)->age;
                $sex = $profile->users[0]->sex->description;
            }

            if(isset($profile->users[1])){
                $spouse = $profile->users[1]->first_name;
                if(!empty($profile->users[1]->middle_name)){
                    $spouse .= " ".$profile->users[1]->middle_name;
                }
                $spouse .= " ".$profile->users[1]->last_name;
                if(!empty($profile->users[1]->maiden_name)){
                    $spouse .= " ".$profile->users[1]->maiden_name;
                }

                $spouse_age = Carbon::parse($profile->users[1]->date_birth)->age;
                $spouse_sex = $profile->users[1]->sex->description;
            }

            if($profile->status == 1){
                $status = 'approved';
            }else if($profile->status == 2){
                $status = 'pending';
            }else if($profile->status == 3){
                $status = 'banned';
            }

            return [
                'id' => $profile->id,
                'full_name' => $full_name,
                'age' => $age,
                'sex' => $sex,
                'spouse' => $spouse,
                'spouse_age' => $spouse_age,
                'spouse_sex' => $spouse_sex,
                'member_since' => Carbon::parse($profile->created_at)->format('d/m/Y'),
                'status' => $status,
                'payment' => $profile->menmbership_id,
            ];
        });

        return response()->json(['data' => $response]);
    }

    public function indexSurrogates(Request $request){

        $profiles =  Profile::with(['users' => function ($q) {
            $q->orderBy('id','ASC');
        }, 'users.sex'])
        ->whereHas('users')
        ->where('type_id', 2)->get();

        $response = $profiles->map(function ($profile) {

            $full_name = "";
            $age = "";
            $sex = "";
            $spouse = "";
            $spouse_age = "";
            $spouse_sex = "";
            $status = "";

            if(isset($profile->users[0])){
                $full_name = $profile->users[0]->first_name;
                if(!empty($profile->users[0]->middle_name)){
                    $full_name .= " ".$profile->users[0]->middle_name;
                }
                $full_name .= " ".$profile->users[0]->last_name;
                if(!empty($profile->users[0]->maiden_name)){
                    $full_name .= " ".$profile->users[0]->maiden_name;
                }

                $age = Carbon::parse($profile->users[0]->date_birth)->age;
                $sex = $profile->users[0]->sex->description;
            }

            if(isset($profile->users[1])){
                $spouse = $profile->users[1]->first_name;
                if(!empty($profile->users[1]->middle_name)){
                    $spouse .= " ".$profile->users[1]->middle_name;
                }
                $spouse .= " ".$profile->users[1]->last_name;
                if(!empty($profile->users[1]->maiden_name)){
                    $spouse .= " ".$profile->users[1]->maiden_name;
                }

                $spouse_age = Carbon::parse($profile->users[1]->date_birth)->age;
                $spouse_sex = $profile->users[1]->sex->description;
            }

            if($profile->status == 1){
                $status = 'approved';
            }else if($profile->status == 2){
                $status = 'pending';
            }else if($profile->status == 3){
                $status = 'banned';
            }

            return [
                'id' => $profile->id,
                'full_name' => $full_name,
                'age' => $age,
                'sex' => $sex,
                'spouse' => $spouse,
                'spouse_age' => $spouse_age,
                'spouse_sex' => $spouse_sex,
                'member_since' => Carbon::parse($profile->created_at)->format('d/m/Y'),
                'status' => $status,
                'payment' => $profile->menmbership_id,
            ];
        });

        return response()->json(['data' => $response]);
    }

    public function checks(Request $request){

		$validator = Validator::make($request->all(),[
            'profile_id' => 'required|exists:profiles,id',
            'check' => 'required|boolean',
            'type' => 'required|in:obgyn,credit'
        ]);

        if( $validator->fails() ){
            return response()->json(['errors' => $validator->errors()],400);
        }

        $profile = Profile::find($request->input('profile_id'));

        if($request->input('type') == 'obgyn'){
            $profile->obgyn_file_check = $request->input('check');
        }

        if($request->input('type') == 'credit'){
            $profile->credit_file_check = $request->input('check');
        }

        $profile->save();

        return response()->json(['data' => 'Profile '.$request->input('type') .' check']);

    }

    public function status(Request $request){

		$validator = Validator::make($request->all(),[
            'profile_id' => 'required|exists:profiles,id',
            'status' => 'required|integer'
        ]);

        if( $validator->fails() ){
            return response()->json(['errors' => $validator->errors()],400);
        }

        $profile = Profile::find($request->input('profile_id'));
        $profile->status = $request->input('status');
        $profile->save();

        return response()->json(['data' => 'Profile status updated']);

    }

    public function show(Request $request){

        $validator = Validator::make($request->all(),[
            'profile_id' => 'required|exists:profiles,id'
        ]);

        if( $validator->fails() ){
            return response()->json(['errors' => $validator->errors()],400);
        }

        $profile = Profile::with(['users.sex'])
        ->where('id',$request->input('profile_id'))->first();

        //questions-answers
        $profileQuestionsAnswers = Question::with(['answers' => function ($q) use ($profile){
            $q->where('profile_id',$profile->id);
            $q->with('option');
        }])
        ->whereHas('answers', function($q) use ($profile) {
            $q->where('profile_id',$profile->id);
        })
        ->get();

        $profile->questions = $profileQuestionsAnswers;

        return response()->json(['data' => $profile]);

    }

}
