<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $questions = Question::with(['options','question_type'])
        ->where('type_id',$request->user_auth->profile->type_id)
        ->orderBy('position', 'asc')
        ->get();

        return response()->json(['data' => $questions]);

    }

    /**
     * Answers
     */
    public function storeAnswer(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'options' => 'required|array',
            'options.*.id' => 'required|exists:options,id',
            'options.*.answer' => 'string|nullable'
        ]);
        if ($validator->fails()){
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $questions_ids = [];
        foreach($request->input('options') as $r_option){
            $question = Question::with(['options'])
            ->whereHas('options', function(Builder $q) use ($r_option) {
                $q->where('id',$r_option['id']);
            })->first();
            if(!in_array($question->id, $questions_ids, true)){
                array_push($questions_ids,$question->id);
            }
        }

        foreach($questions_ids as $ques_id){
            Answer::where('profile_id',$request->user_auth->profile_id)->where('question_id',$ques_id)->delete();
        }

        foreach ($request->input('options') as $r_option) {
            $question = Question::whereHas('options', function(Builder $q) use ($r_option) {
                $q->where('id',$r_option['id']);
            })->first();

            Answer::create([
                'profile_id' => $request->user_auth->profile_id,
                'question_id' => $question->id,
                'option_id' => $r_option['id'],
                'answer' => $r_option['answer']
            ]);

            Profile::where('id', $request->user_auth->profile_id)
                ->update(['question_position' => $question->position]);
        }

        return response()->json(['data' => 'Answers/created']);

    }


    public function profileAnswers(Request $request){

        $user_auth = $request->user_auth;
        $questions = Question::with(['answers' => function($q) use ($user_auth){
            $q->where('profile_id', $user_auth->profile_id)
            ->with(['option']);
        }])
        ->where('type_id',$user_auth->profile->type_id)
        ->orderBy('position', 'asc')
        ->get();

        return response()->json(['data' => $questions]);

    }

}
