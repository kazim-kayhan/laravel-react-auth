<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $registerRequest)
    {
        try {
            $user = User::create([
            'name'=>$registerRequest->input('name'),
            'email'=>$registerRequest->input('email'),
            'password'=>Hash::make($registerRequest->input('password'))
            ]);
            return $user;
        } catch (\Exception $exception) {
            return response(['message'=>$exception->getMessage()], status:400);
        }
    }

    public function login(Request $request)
    {
        try {
            if (Auth::attempt($request->only('email', 'password'))) {
                /** @var User $user */
                $user = Auth::user();
                $token = $user->createToken('app')->accessToken;
                return response(['message'=>'success','token'=>$token,'user'=>$user]);
            }
        } catch (\Exception $exception) {
            return response(['message'=>$exception->getMessage()], status:400);
        }
        return response(['message'=>'Invalid username/password'], status:401);
    }

    public function user()
    {
        return Auth::user();
    }
}
