<?php

namespace App\Http\Traits;
use App\Models\Question;
use App\Models\Profile;

trait ProfileProgressTrait {

    public function profileProgress($profile, $user_auth) {

        $profile_id = $profile->id;
        $user_id = $user_auth->id;
        $last_question = Question::select('id', 'position')->where('type_id',$profile->type_id)->orderBy('position','DESC')->first();
        $check_questions = false;
        if($profile->question_position == $last_question->position){
            $check_questions = true;
        }

        /* Progres
        nickname
        location
        relationship
        questions (Solo aquí cambia el question_step)
        bio
        profile_picture
        spuse_check (IP)
        spouse
        obgyn_letter -- 2
        credit_report  -- 2
        done
        */

        $step = "done";
        $question_step = 0;
        if($profile->type_id == 1){

            $step_spouse = false;
            if(isset($profile->marital_id) and $profile->marital_id != 1 and $profile->spouse_check == true){

                $profile_users = Profile::with(['users' => function($q) use ($user_id){
                    $q->where('id','!=',$user_id);
                }])
                ->where('id',$profile_id)
                ->first();

                if(count($profile_users->users) == 0){
                    $step_spouse = true;
                }
            }

            if($step_spouse == true){
                $step = "spouse";
            }

            if(is_null($profile->spouse_check) and $profile->marital_id != 1){
                $step = "spouse_check";
            }

            //el check sera campo vacío
            if(!isset($profile->picture)){
                $step = "profile_picture";
            }

            if(empty($profile->bio)){
                $step = "bio";
            }

            if($check_questions == false){
                $step = "questions";
                $question_step = $profile->question_position;
            }

            if(empty($profile->marital_id)){
                $step = "relationship";
            }

            if(empty($profile->city)){
                $step = "location";
            }

            if(empty($profile->nickname)){
                $step = "nickname";
            }

        }else if($profile->type_id == 2){

            if(empty($profile->picture)){
                $step = "profile_picture";
            }

            //el check sera campo vacío
            if(empty($profile->credit_file)){
                $step = "credit_report";
            }

            //el check sera campo vacío
            if(!isset($profile->obgyn_file)){
                $step = "obgyn_letter";
            }

            if(empty($profile->bio)){
                $step = "bio";
            }

            if($check_questions == false){
                $step = "questions";
                $question_step = $profile->question_position;
            }

            if(empty($profile->marital_id)){
                $step = "relationship";
            }

            if(empty($profile->city)){
                $step = "location";
            }

            if(empty($profile->nickname)){
                $step = "nickname";
            }

        }

        $progress = [
            "step" => $step,
            "question_step" => $question_step
        ];

        return $progress;


    }
}
