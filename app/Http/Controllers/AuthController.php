<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\VerifyUser;
use App\Models\Profile;
use App\Http\Requests\UserRequest;
use App\Mail\VerifyAccount;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

use App\Http\Traits\ProfileProgressTrait;
class AuthController extends Controller
{
    use ProfileProgressTrait;

    public function register(UserRequest $request){

        //Rol ID == Type ID

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'maiden_name' => $request->input('maiden_name'),
            'sex_id' => $request->input('sex_id'),
            'date_birth' => $request->input('date_birth'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->get('password')),
            'rol_id' => $request->input('type_id')
        ]);
        //$token = JWTAuth::fromUser($user);

        //Create profile
        if($request->input('type_id') == 1){
            $profile = Profile::create([
                'type_id' => $request->input('type_id'),
                'status' => 1
            ]);
        }else{
            $profile = Profile::create([
                'type_id' => $request->input('type_id'),
                'status' => 2
            ]);
        }

        $user->profile_id = $profile->id;
        $user->save();

        /**Crear token para verificacion de email */
        $verify_token = Str::random(60);
        while(VerifyUser::where('token',$verify_token)->first()){
            $verify_token = Str::random(60);
        }
        VerifyUser::create([
            'email' => $user->email,
            'token' => $verify_token
        ]);

        try {
            Mail::to($user->email)->send(new VerifyAccount($user));
        } catch (\Exception $e) {
            Log::error('Auth Register Email: '.$e->getMessage());
            return response()->json(['data' => 'user created', 'note' => 'Error send Email']);
        }

        return response()->json(['data' => 'user/profile created']);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::with(['profile'])
        ->where('email',$request->input('email'))->first();
        if (!isset($user->email_verified_at)) {
            return response()->json(["error" => 'You need to confirm your account. We have sent you an activation code, please check your email'],400);
        }

        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => ' Email and password do not match'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $progress = $this->profileProgress($user->profile, $user);

        return response()->json(["data" => [
            'user' => $user,
            'token' => $token,
            'progress' => $progress
        ]]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                    return response()->json(['error' => 'user_not_found'], 404);
            }
            } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                    return response()->json(['error' => 'token_expired'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                    return response()->json(['error' => 'token_invalid'], $e->getStatusCode());
            } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
                    return response()->json(['error' => 'token_absent'], $e->getStatusCode());
            }
        return response()->json(["data" => $user]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        try {
            JWTAuth::parseToken()->invalidate();
            return response()->json(['data' => 'logout'], 200);
        } catch (JWTException $exception) {
            Log::error('Auth Logout: '.$exception->getMessage());
            return response()->json([
                'data' => 'Sorry, the user cannot be logged out',
            ], 500);
        }
    }

    public function tokenRefresh()
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            $token = JWTAuth::getToken();
            if (!$token) {
                throw new BadRequestHtttpException('Token not provided');
            }
            try {
                $token = JWTAuth::refresh($token);
            } catch (TokenInvalidException $e) {
                throw new AccessDeniedHttpException('The token is invalid');
            }
            return $token;
        }
    }

    /**
     * Verify User Email-Token
     * @urlParam token required
     */
    public function verifyUser($token){

        $verifyUser = VerifyUser::where('token', $token)->first();

        if(isset($verifyUser)){
            $user = $verifyUser->user;
            if(!$user->email_verified_at) {
                $verifyUser->user->email_verified_at = Carbon::now();
                $verifyUser->user->save();
            }
            $q = "DELETE FROM verify_users where token = ?";
            DB::delete($q, [$token]);
        }else{
            return response()->json(['error' => 'Sorry your confirmation code cannot be identified.'], 404);
        }

        $token = JWTAuth::fromUser($user);
        $profile = $user->profile;
        return response()->json(["data" => [
            'user' => $user,
            'token' => $token,
            'progress' => $this->profileProgress($profile, $user)
        ]]);
    }

    /**
     * Resend Verify User Email-Token
     * @urlParam token required
     */
    public function verifyTokenSend(Request $request){

        $verifyUser = VerifyUser::where('email', $request->input('email'))->first();
        if(isset($verifyUser)){
            $user = $verifyUser->user;
        }else{
            $user = User::where('email',$request->input('email'))->first();
            if(!$user){
                return response()->json(['error' => 'email not found'], 404);
            }

            $verify_token = Str::random(60);
            while(VerifyUser::where('token',$verify_token)->first()){
                $verify_token = Str::random(60);
            }
            VerifyUser::create([
                'email' => $user->email,
                'token' => $verify_token
            ]);
        }

        try {
            Mail::to($user->email)->send(new VerifyAccount($user));
        } catch (\Exception $e) {
            Log::error('Auth Register Email: '.$e->getMessage());
            return response()->json(['error' => 'Error send Email'], 400);
        }

        return response()->json(['data' => 'email sent']);

    }

}
