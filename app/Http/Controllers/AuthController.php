<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barber;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'role_id' => 'required',
      'fname' => 'required',
      'lname' => 'required',
      'email' => 'required',
      'password' => 'required'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors());
    }

    $user = User::create([
      'role_id' => $request->role_id,
      'fname' => $request->fname,
      'lname' => $request->lname,
      'email' => $request->email,
      'password' => $request->password,
      // 'password' => bcrypt($request->passwword),
      'remember_token' => Str::random(60)
    ]);

    $idbaru = $user->id_user;

    if ($request->role_id == 1) {
      Customer::create([
        'fname' => $request->fname,
        'lname' => $request->lname,
        'customer_id' => $idbaru,
      ]);
    }

    if ($request->role_id == 2) {
      Barber::create([
        'fname' => $request->fname,
        'lname' => $request->lname,
        'barber_id' => $idbaru,
      ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
      'message' => 'register success',
      'data' => [
        'role_id' => $user->role_id,
        'email' => $user->email,
        'fname' => $user->fname,
        'lname' => $user->lname
      ],
      'access_token' => $token,
      'token_type' => 'Bearer',
    ]);
  }

  public function login(Request $request)
  {
    if (!Auth::attempt($request->only('email', 'password'))) {
      return response()->json([
        'message' => 'Unauthorized'
      ], 401);
    }

    $user = User::where('email', $request->email)->firstOrFail();
    $token = $user->createToken('auth_token')->plainTextToken;

    $role = Auth::user()->roleuser->id_role;

    if ($role == 1) {
      return response()->json([
        'message' => 'Login success',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'data' => [
          'email' => $user->email,
          'fname' => $user->fname,
          'lname' => $user->lname,
          'role_id' => $user->role_id
        ]
      ]);
    }

    if ($role == 2) {
      return response()->json([
        'message' => 'Login success',
        'access_token' => $token,
        'token_type' => 'Bearer',
        'data' => [
          'email' => $user->email,
          'fname' => $user->fname,
          'lname' => $user->lname,
          'role_id' => $user->role_id
        ]
      ]);
    }

  }

  public function logout(Request $request)
  {
    $request->user()->currentAccessToken()->delete();
    return response()->json([
      'message' => 'logout success'
    ]);
  }
}
