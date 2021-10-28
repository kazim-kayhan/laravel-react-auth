<?php

namespace App\Http\Controllers;

use PDO;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\ForgotRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotController extends Controller
{
    public function forgot(ForgotRequest $request)
    {
        $email = $request->input('email');
        if (!User::where('email', '$email')->doesntExist()) {
            return response([
                'message'=>'User doesn\'t exist in the users table'
            ], status:400);
        }
        $token = Str::random(10);
        try {
            DB::table('password_resets')->insert([
                'email'=>$email,
                'token'=>$token
            ]);

            Mail::send('Mail.forgot', ['token'=>$token], function ($message) use ($email) {
                $message->to($email);
                $message->subject('Reset password');
            });

            return response([
                'message'=>'Check your email'
            ]);
        } catch (\Exception $exception) {
            return response([
                'message'=>$exception->getMessage()
            ], status:400);
        }
    }

    public function reset(ResetRequest $resetRequest)
    {
        $token = $resetRequest->input('token');
        if (!$passResset = DB::table('password_resets')->where('token', $token)->first()) {
            return response([
                'message'=>'Invalid token'
            ], status:400);
        }

        /** @var User $user */
        if (!$user = User::where('email', $passResset->email)->first()) {
            return response([
            'message'=>'User doesn\'t exist'
        ], status:404);
        }

        $user->password = Hash::make($resetRequest->input('password'));
        $user->save();

        return response([
            'message'=>'success'
        ]);
    }
}
