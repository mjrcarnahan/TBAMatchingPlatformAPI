<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CheckRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ProfileProgressTrait;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;
class ProfileController extends Controller
{

    use ProfileProgressTrait;

    public function index(Request $request)
    {

        $profile =  $request->user_auth->profile;
        //1 man, 2 woman
        $sex_id = $request->user_auth->sex_id;
        //null, 1 hetero, 2 homo
        $sex_relationship = isset($profile->relationship) ? $profile->relationship : 0;

        $profiles = Profile::where('type_id', '!=', $profile->type_id);

        //Solo perfiles activos
        $profiles->where('status',1);

        //intendent (ve surrogates)
        if ($profile->type_id == 1) {

            //Free
            if ($profile->membership_id == 1) {

                $profiles = $profiles->select('id', 'nickname', 'picture', 'picture_blur', 'state', 'bio', 'type_id', 'membership_id')
                    ->manyTimesSurrogate()
                    ->inRandomOrder()->get()->makeHidden(['report_url', 'obgyn_url']);

                $profiles->map(function ($profile_item) use ($profile) {

                    $profile_item->age = isset($profile_item->users[0]) ? Carbon::parse($profile_item->users[0]->date_birth)->age : 0;

                    $profile_item->showPicture(0);
                    $profile_item->near($profile->state);

                    $profile_item->many_times_surrogate = null;
                    if (!empty($profile_item->answers)) {
                        foreach ($profile_item->answers as $answer) {
                            $profile_item->many_times_surrogate = $answer->option->title;
                        }
                    }

                    unset($profile_item->state);
                    unset($profile_item->answers);

                    return $profile_item;
                });

                //Pro
            } else if ($profile->membership_id == 2) {

                $profiles = $profiles->with(['answers'])
                    ->get()->makeHidden(['report_url', 'obgyn_url']);

                //Match
                $answers = Answer::where('profile_id', $profile->id)->get();

                //relationship user
                /*
                $user_relationship = User::where('profile_id', $profile->id)->where('id', '!=', $request->user_auth->id)->first();
                $sex_relationship = 0;
                $sex_id = $request->user_auth->sex_id;
                if ($user_relationship) {
                    $sex_relationship = $user_relationship->sex_id;
                }
                */

                $compesation = null;
                $feelings_about_termination = 0;
                $feelings_about_selective = 0;
                foreach ($answers as $answer) {

                    //compesation
                    // option 23 - 28
                    if ($answer->question_id == 7) {
                        $compesation = $answer->option_id;
                    }
                    //feelings about termination
                    //option 15 Will never terminate
                    //option 16 Might terminate
                    if ($answer->question_id == 4) {
                        $feelings_about_termination = $answer->option_id;
                    }

                    //feelings about selective
                    //option 17 Will never reduce
                    //option 18 Open to reducing
                    if ($answer->question_id == 5) {
                        $feelings_about_selective = $answer->option_id;
                    }
                }

                $compesation_value = 0;
                if ($compesation != null) {
                    if ($compesation == 23) $compesation_value = 1;
                    if ($compesation == 24) $compesation_value = 2;
                    if ($compesation == 25) $compesation_value = 3;
                    if ($compesation == 26) $compesation_value = 4;
                    if ($compesation == 27) $compesation_value = 5;
                    if ($compesation == 28) $compesation_value = 6;
                }

                $profiles->map(function ($profile_item) use ($profile, $compesation_value, $feelings_about_termination, $feelings_about_selective, $sex_id, $sex_relationship) {

                    $profile_item->age = isset($profile_item->users[0]) ? Carbon::parse($profile_item->users[0]->date_birth)->age : 0;

                    $profile_item->showPicture(1);
                    $profile_item->near($profile->state);

                    $profile_item->recommended = false;
                    $profile_item->extra_point = 0;

                    if ($profile_item->near) {
                        $profile_item->extra_point++;
                    }

                    $profile_item->many_times_surrogate = null;
                    $compesation_surrogate = null;
                    $check_temination = false;
                    $check_selective = false;
                    $check_family = false;
                    $family_type = [];

                    if (!empty($profile_item->answers)) {
                        foreach ($profile_item->answers as $answer) {
                            //many times surrogates
                            if ($answer->question_id == 23) {
                                $profile_item->many_times_surrogate = $answer->option->title;
                            }

                            //compesation
                            if ($answer->question_id == 29) {
                                $compesation_surrogate = $answer->option_id;
                            }

                            //family_type
                            //option 64 Single
                            //option 65 Heterosexual couples
                            //option 66 Homosexual (female) couples
                            //option 67 Homosexual (male) couples
                            if ($answer->question_id == 24) {
                                array_push($family_type, $answer->option_id);
                            }

                            //feelings about termination
                            //option 72 Will never terminate
                            //option 73 Might terminate
                            if ($answer->question_id == 26) {
                                if ($answer->option_id  == 72 and $feelings_about_termination == 15) {
                                    $check_temination = true;
                                }
                                if ($answer->option_id == 73 and $feelings_about_termination == 16) {
                                    $check_temination = true;
                                }
                            }

                            //feelings about selective
                            //option 74 Will never reduce
                            //option 75 Open to reducing
                            if ($answer->question_id == 27) {
                                if ($answer->option_id  == 74 and $feelings_about_selective == 17) {
                                    $check_selective = true;
                                }
                                if ($answer->option_id == 75 and $feelings_about_selective == 18) {
                                    $check_selective = true;
                                }
                            }

                        }
                    }

                    if (count($family_type) == 4) {
                        $check_family = true;
                    } else {
                        if (in_array(64, $family_type) and $profile->marital_id == 1) {
                            $check_family = true;
                        }
                        if ($profile->marital_id != 1) {
                            //homo
                            if ($sex_relationship == 2) {
                                //homo man
                                if (in_array(67, $family_type) and $sex_id == 1) {
                                    $check_family = true;
                                }
                                //home woman
                                if (in_array(66, $family_type) and $sex_id == 2) {
                                    $check_family = true;
                                }
                            } else {
                                //hetero
                                if (in_array(65, $family_type)) {
                                    $check_family = true;
                                }
                            }
                        }
                    }

                    //POINTS
                    if (($check_temination and $check_selective and $check_family)) {
                        $profile_item->extra_point += 10;
                        $profile_item->recommended = true;
                    }else{
                        if($check_temination){
                            $profile_item->extra_point += 3;
                        }
                        if($check_selective){
                            $profile_item->extra_point += 3;
                        }
                        if($check_family){
                            $profile_item->extra_point += 3;
                        }
                    }

                    if ($compesation_surrogate != null) {
                        //options 80-85

                        $compesation_surro_value = 0;
                        if ($compesation_surrogate == 80) $compesation_surro_value = 1;
                        if ($compesation_surrogate == 81) $compesation_surro_value = 2;
                        if ($compesation_surrogate == 82) $compesation_surro_value = 3;
                        if ($compesation_surrogate == 83) $compesation_surro_value = 4;
                        if ($compesation_surrogate == 84) $compesation_surro_value = 5;
                        if ($compesation_surrogate == 85) $compesation_surro_value = 6;

                        // compare to 23-28
                        if ($compesation_value <= $compesation_surro_value) {
                            $profile_item->extra_point++;
                        }
                    }

                    unset($profile_item->answers);
                    unset($profile_item->users);
                    return $profile_item;
                });

                $profiles = $profiles->sortByDesc('extra_point');
                $profiles = $profiles->values();
            }

            //surrogate (ver perfiles intendent)
        } else if ($profile->type_id == 2) {

            $profiles = $profiles->with(['answers','users'])
                ->get()->makeHidden(['report_url', 'obgyn_url']);

            //Match
            $answers = Answer::where('profile_id', $profile->id)->get();

            $compesation = null;
            $family_type = [];
            $feelings_about_termination = 0;
            $feelings_about_selective = 0;
            foreach ($answers as $answer) {

                //compesation
                // option 80-85
                if ($answer->question_id == 29) {
                    $compesation = $answer->option_id;
                }
                //feelings about termination
                //option 72 Will never terminate
                //option 73 Might terminate
                if ($answer->question_id == 26) {
                    $feelings_about_termination = $answer->option_id;
                }

                //feelings about selective
                //option 74 Will never reduce
                //option 75 Open to reducing
                if ($answer->question_id == 27) {
                    $feelings_about_selective = $answer->option_id;
                }

                //family_type
                //option 64 Single
                //option 65 Heterosexual couples
                //option 66 Homosexual (female) couples
                //option 67 Homosexual (male) couples
                if ($answer->question_id == 24) {
                    array_push($family_type, $answer->option_id);
                }

            }

            $compesation_value = 0;
            if ($compesation != null) {
                if ($compesation == 80) $compesation_value = 1;
                if ($compesation == 81) $compesation_value = 2;
                if ($compesation == 82) $compesation_value = 3;
                if ($compesation == 83) $compesation_value = 4;
                if ($compesation == 84) $compesation_value = 5;
                if ($compesation == 85) $compesation_value = 6;
            }

            $profiles->map(function ($profile_item) use ($profile, $compesation_value, $feelings_about_termination, $feelings_about_selective, $family_type, $sex_relationship) {
                $profile_item->age = isset($profile_item->users[0]) ? Carbon::parse($profile_item->users[0]->date_birth)->age : 0;

                $profile_item->showPicture(1);
                $profile_item->near($profile->state);

                $profile_item->recommended = false;
                $profile_item->extra_point = 0;

                if ($profile_item->near) {
                    $profile_item->extra_point++;
                }

                $compesation_intendent = null;
                $check_temination = false;
                $check_selective = false;

                if (!empty($profile_item->answers)) {
                    foreach ($profile_item->answers as $answer) {
                        //compesation
                        if ($answer->question_id == 7) {
                            $compesation_intendent = $answer->option_id;
                        }

                        //feelings about termination
                        //option 15 Will never terminate
                        //option 16 Might terminate
                        if ($answer->question_id == 4) {
                            if ($answer->option_id  == 15 and $feelings_about_termination == 72) {
                                $check_temination = true;
                            }
                            if ($answer->option_id == 16 and $feelings_about_termination == 73) {
                                $check_temination = true;
                            }
                        }

                        //feelings about selective
                        //option 17 Will never reduce
                        //option 18 Open to reducing
                        if ($answer->question_id == 5) {
                            if ($answer->option_id  == 17 and $feelings_about_selective == 74) {
                                $check_selective = true;
                            }
                            if ($answer->option_id == 18 and $feelings_about_selective == 75) {
                                $check_selective = true;
                            }
                        }

                    }
                }

                //family type
                //user
                /*
                $sex_relationship = [];
                foreach($profile_item->users as $user){
                    array_push($sex_relationship, $user->sex_id);
                }

                $check_family = false;
                if(isset($sex_id)){
                    if (count($family_type) == 4) {
                        $check_family = true;
                    } else {
                        if (in_array(64, $family_type) and $profile_item->marital_id == 1) {
                            $check_family = true;
                        }
                        if ($profile_item->marital_id != 1) {
                            if(count($sex_relationship) == 2){
                                //homo
                                if ($sex_relationship[0] == $sex_relationship[1]) {
                                    if (in_array(67, $family_type) and $sex_relationship[0] == 1) {
                                        $check_family = true;
                                    }
                                    if (in_array(66, $family_type) and $sex_relationship[0] == 2) {
                                        $check_family = true;
                                    }
                                    //hetero
                                } else {
                                    if (in_array(65, $family_type)) {
                                        $check_family = true;
                                    }
                                }
                            }
                        }
                    }
                }
                */

                $check_family = false;
                $user_sex = isset($profile_item->users[0]) ? $profile_item->users[0]->sex_id : 0;

                if(isset($sex_id)){
                    if (count($family_type) == 4) {
                        $check_family = true;
                    } else {
                        if (in_array(64, $family_type) and $profile_item->marital_id == 1) {
                            $check_family = true;
                        }
                        if ($profile_item->marital_id != 1) {
                            if($profile_item->relationship == 2){
                            //homo
                            if (in_array(67, $family_type) and $user_sex == 1) {
                                $check_family = true;
                            }
                            if (in_array(66, $family_type) and $user_sex == 2) {
                                $check_family = true;
                            }
                            //hetero
                            } else {
                                if (in_array(65, $family_type)) {
                                    $check_family = true;
                                }
                            }
                        }
                    }
                }

                //POINTS
                if (($check_temination and $check_selective and $check_family)) {
                    $profile_item->extra_point += 10;
                    $profile_item->recommended = true;
                }else{
                    if($check_temination){
                        $profile_item->extra_point += 3;
                    }
                    if($check_selective){
                        $profile_item->extra_point += 3;
                    }
                    if($check_family){
                        $profile_item->extra_point += 3;
                    }
                }

                if ($compesation_intendent != null) {
                    //options 23-28
                    $compesation_inten_value = 0;
                    if ($compesation_intendent == 23) $compesation_inten_value = 1;
                    if ($compesation_intendent == 24) $compesation_inten_value = 2;
                    if ($compesation_intendent == 25) $compesation_inten_value = 3;
                    if ($compesation_intendent == 26) $compesation_inten_value = 4;
                    if ($compesation_intendent == 27) $compesation_inten_value = 5;
                    if ($compesation_intendent == 28) $compesation_inten_value = 6;

                    // compare to 80-85
                    if ($compesation_value == $compesation_inten_value and $compesation_inten_value != 0) {
                        $profile_item->extra_point++;
                    }
                }

                unset($profile_item->users);
                unset($profile_item->answers);
                return $profile_item;
            });

            $profiles = $profiles->sortByDesc('extra_point');
            $profiles = $profiles->values();
        }

        return response()->json(['data' => $profiles]);
    }

    public function show(Request $request, $profile_id)
    {

        $profile_auth = $request->user_auth->profile;

        if ($profile_auth->type_id == 1) {
            $profile = Profile::manyTimesSurrogate()->where('id', $profile_id)->firstOrFail()->makeHidden(['report_url', 'obgyn_url']);
        } else if ($profile_auth->type_id == 2) {
            $profile = Profile::findOrFail($profile_id)->makeHidden(['report_url', 'obgyn_url']);
        }

        //Intendent (no permite en free ver perfiles, y en pro tampoco ver perfiles de otro intendent)
        if ($profile_auth->type_id == 1) {
            if ($profile_auth->membership_id == 1 || $profile_auth->type_id == $profile->type_id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            //surrogate no ver perfiles de otros surrogates
        } else if ($profile_auth->type_id  == 2) {
            if ($profile_auth->type_id == $profile->type_id) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        }

        //version pro intendent - surrogate
        $profile->age = isset($profile->users[0]) ? Carbon::parse($profile->users[0]->date_birth)->age : 0;
        $profile->showPicture(1);
        $profile->near($profile_auth->state);

        if ($profile_auth->type_id == 1) {
            $profile->many_times_surrogate = null;
            if (!empty($profile->answers)) {
                foreach ($profile->answers as $answer) {
                    $profile->many_times_surrogate = $answer->option->title;
                }
            }
            unset($profile->answers);
        }

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

    //Endpoints To Profiles owner

    public function showMe(Request $request)
    {
        $profile = $request->user_auth->profile;
        $profile->showPicture(1);
        $progress = $this->profileProgress($profile, $request->user_auth);

        return response()->json(['data' => ['profile' => $profile, 'progress' => $progress]]);
    }

    public function agree(CheckRequest $request)
    {
        $request->user_auth->profile->agree = $request->input('check');
        $request->user_auth->profile->save();
        return response()->json(['data' => 'Agree Updated']);
    }

    public function update(Request $request)
    {

        if (
            $request->has('id') || $request->has('type_id')
            || $request->has('question_position') || $request->has('membership_id')
        ) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $profile = $request->user_auth->profile;

        if ($request->has('picture')) {
            if (empty($request->input('picture'))) {
                $profile->picture = ($profile->type_id == 2) ? null : "";
                $profile->save();
                if (!empty($profile->picture)) {
                    Storage::disk('public')->delete($profile->picture);
                }
            }
        } else if ($request->has('obgyn_file')) {
            if (empty($request->input('obgyn_file'))) {
                $profile->obgyn_file = ($profile->type_id == 1) ? null : "";
                $profile->save();
                if (!empty($profile->obgyn_file)) {
                    Storage::disk('public')->delete($profile->obgyn_file);
                }
            }
        } else {
            $profile->fill($request->except('user_auth'));
            $profile->save();
        }

        return response()->json(['data' => 'Profile Updated']);
    }

    public function picture(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'picture' => 'required|image'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $profile = $request->user_auth->profile;

        if (!empty($profile->picture)) {
            Storage::disk('public')->delete($profile->picture);
        }
        if (!empty($profile->picture_blur)) {
            Storage::disk('public')->delete($profile->picture_blur);
        }

        $filename = $request->user_auth->profile_id . "_" . uniqid() . "." . $request->file('picture')->getClientOriginalExtension();
        $path = $request->file('picture')->storeAs('/profiles/pictures', $filename, 'public');
        $profile->picture = $path;

        $img = Image::make($request->file('picture'))->pixelate(20)->blur(60)->orientate()->save();
        $file_path = 'profiles/pictures/' . $request->user_auth->profile_id . '_2' . uniqid() . '.' . $request->file('picture')->getClientOriginalExtension();
        Storage::disk('public')->put($file_path, $img);
        $profile->picture_blur = $file_path;
        $profile->save();

        return response()->json(['data' => 'Profile Picture Updated']);
    }

    public function creditReport(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $filename = $request->user_auth->profile_id . "_" . uniqid() . "." . $request->file('file')->getClientOriginalExtension();
        $path = $request->file('file')->storeAs('/profiles/credit', $filename, 'public');
        $request->user_auth->profile->credit_file = $path;
        $request->user_auth->profile->save();

        return response()->json(['data' => 'Profile Credit Report Updated']);
    }

    public function obgynLetter(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'file' => 'required'
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $filename = $request->user_auth->profile_id . "_" . uniqid() . "." . $request->file('file')->getClientOriginalExtension();
        $path = $request->file('file')->storeAs('/profiles/obgyn', $filename, 'public');
        $request->user_auth->profile->obgyn_file = $path;
        $request->user_auth->profile->save();

        return response()->json(['data' => 'Profile Obgyn Letter Updated']);
    }
}
