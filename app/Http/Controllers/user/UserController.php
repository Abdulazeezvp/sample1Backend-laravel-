<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UserController extends Controller
{
    //
    public function createUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate(
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|email|max:255',
                    'password' => 'required|string|min:6',
                    'mobile' => 'required|string|min:8',
                ]
            );
            $user = User::create(
                $request->all()
            );
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['msg' => 'error', 'status' => false, 'statusCode' => 500]);
        }
        DB::commit();
        return response()->json(['msg' => 'created new user', 'status' => true, 'statusCode' => 200]);
    }

    public function userLogin(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate(
                [
                    'email' => 'required|string|email|max:255',
                    'password' => 'required|string|min:6',
                ]
            );
            $user = User::where('email', $request->email)->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    $token = $user->createToken('Token for user Angular')->accessToken;
                    // dd('here11');
                    $data = [
                        "user" => $user,
                        "token" => $token
                    ];
                    DB::commit();
                    return response()->json(['msg' => 'login successfull', 'status' => true, 'statusCode' => 200, 'data' => $data]);
                } else {
                    return response()->json(['msg' => 'Invalid Credintilas', 'status' => false, 'statusCode' => 401, 'data' => '']);
                }
            } else {
                return response()->json(['msg' => 'Not a valid user name', 'status' => false, 'statusCode' => 401, 'data' => '']);
            }
        } catch (Throwable $th) {
            return response($th);
            DB::rollBack();
            return response()->json(['msg' => 'Something went wrong', 'status' => false, 'statusCode' => 500, 'data' => '']);
        }
    }
}
