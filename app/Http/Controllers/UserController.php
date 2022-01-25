<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SpouseRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Mail\ResetPassword;
use App\Models\User;
use App\Models\VerifyUser;
use Carbon\Carbon;
use JWTAuth;

class UserController extends Controller
{

    /**Change password */
    /**
     * send Password Reset Link Email
     * @bodyParam email email required .
     */
    public function sendEmail(Request $request){

		$validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
        ]);

        if( $validator->fails() ){
            return response()->json(['errors' => $validator->errors()],400);
        }

        $email = $request->input('email');

        $oldToken = DB::table('password_resets')->where('email',$email)->first();
		if(isset($oldToken->token)){
			$token = $oldToken->token;
		}else{
            $token = Str::random(60);
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        }

        try {
            Mail::to($email)->send(new ResetPassword($token));
        } catch (\Exception $e) {
            Log::error('Reset Password: '.$e->getMessage());
            return response()->json([
                'error' => 'Error sending Email '.$e->getMessage()
            ], 400);
        }

        return response()->json([
            'data' => 'Reset Email password is send successfully'
        ], 200);

    }


    /**
     * Change a user's password by password reset
     * @bodyParam password string required
     * @bodyParam password_confirmation string required
     * @bodyParam token string required
     */
    public function changePassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'password' => 'required|confirmed',
            'token' => 'required|exists:password_resets,token',
        ]);

        if ($validator->fails()) return response()->json(['errors' => $validator->errors()], 400);

        $pass_reset = DB::table('password_resets')->where('token', $request->input('token'))->first();
        $user = User::where('email',$pass_reset->email)->first();
        $user->password = Hash::make($request->input('password'));

        //auto-verify user
        if(!$user->email_verified_at){

            $verifyUser = VerifyUser::where('email', $pass_reset->email)->first();
            if(isset($verifyUser)){
                $q = "DELETE FROM verify_users where email = ?";
                DB::delete($q, [$pass_reset->email]);
            }

            $user->email_verified_at = Carbon::now();
        }
        $user->save();

        $q = "DELETE FROM password_resets where token = ?";
        DB::delete($q, [$request->input('token')]);

        return response()->json(['data' => 'Password Changed'], 200);
    }

    /********Change Password */

    /**Owner User*/

    public function spouse(SpouseRequest $request){

        $user_auth = JWTAuth::user();
        $temp_pass = Str::random(60);
        $user = User::create([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'maiden_name' => $request->input('maiden_name'),
            'sex_id' => $request->input('sex_id'),
            'date_birth' => $request->input('date_birth'),
            'email' => $request->input('email'),
            'password' => Hash::make($temp_pass),
            'profile_id' => $user_auth->profile_id,
            'rol_id' => $user_auth->rol_id
        ]);

        $user->profile->marital_id = $request->input('marital_id');
        $user->profile->save();

        //Surrogate
        if($user_auth->rol_id == 1){
            /**Crear Token de envio de cambio de contraseña */
            $token = Str::random(60);
            DB::table('password_resets')->insert([
                'email' => $request->input('email'),
                'token' => $token,
                'created_at' => Carbon::now()
            ]);

            try {
                Mail::to($user->email)->send(new ResetPassword($token));
            } catch (\Exception $e) {
                Log::error('Auth Register Spouse Email passw: '.$e->getMessage());
                return response()->json(['data' => 'user created', 'note' => 'Error send Email']);
            }
        }

        return response()->json(['data' => 'user/spouse created']);

    }
}
