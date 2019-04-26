<?php
/**
 * Created by PhpStorm.
 * User: cuongnq
 * Date: 26/04/2019
 * Time: 11:23
 */

namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    /**
     * @return string
     */
    public static function index()
    {
        $users = User::all();
        if (!$users) {
            return response()->json(['message' => 'Not exist any user'], 500);
        } else {
            return response()->json(['User List' => $users],200);
        }
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function add(Request $request)
    {
        $inputs = $request->all();

        // Validate type 1
//        $this->validate($request, [
//            'name' => 'required|string|max:255',
//            'email' => 'required|string|email|max:255|unique:users',
//            'password' => 'required|string|min:6',
//        ]);

        // Validate type 2
        $validator = Validator::make($inputs, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->all());
        } else {
            DB::beginTransaction();
            try {
                User::create([
                    'user_name' => $inputs['name'],
                    'email' => $inputs['email'],
                    'password' => $inputs['password'],
                ]);
                DB::commit();
                return response()->json([
                    'message' => 'Create user success',
                    'user' => User::whereEmail($inputs['email'])->first()],200);
            } catch (\Exception $e) {
                DB::rollback();
                return $e->getMessage();
            }
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function edit($id, Request $request)
    {
        $inputs = $request->all();
        $user = User::find($id ?? null);
        if (!$user) {
            return response()->json(['message' => 'User id is not exist'],500);
        } else {
            DB::beginTransaction();
            try {
                $user->user_name = $inputs['name'] ?? $user->user_name;
                $user->email = $inputs['email'] ?? $user->email;
                $user->password = $inputs['password'] ?? $user->password;
                $user->save();
                DB::commit();
                return response()->json([
                    'user' => $user,
                    'message' => "Updated User $id success"
                ],200);
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public static function delete($id)
    {
        $user = User::find($id ?? null);
        if (!$user) {
            return response()->json(['message' => 'User id is not exist'],500);
        } else {
            DB::beginTransaction();
            try {
                User:: find($id)->delete();
                DB::commit();
                return "Deleted User $id success";
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }
}