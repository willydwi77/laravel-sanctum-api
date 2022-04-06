<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credential = [
                'email' => $request->email,
                'password' => $request->password
            ];

            if (Auth::attempt($credential)) {
                $user = Auth::user();
                $token = $user->createToken('auth-token')->accessToken;
                
                return $this->sendResponse($token);
            } else {
                return $this->sendError(
                    errorMessages: 'Email atau Password tidak valid'
                );
            }
        } catch (QueryException $err) {
            return $this->sendError(
                errorMessages: $err
            );
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6|confirmed'
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    errorMessages: $validator->errors()
                );
            }

            $store = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            if ($store) {
                $token = $store->createToken('auth-token')->accessToken;

                return $this->sendResponse($token);
            } else {
                return $this->sendError(
                    errorMessages: 'Gagal registrasi user baru'
                );
            }
        } catch (QueryException $err) {
            return $this->sendError(
                errorMessages: $err
            );
        }
    }
}
