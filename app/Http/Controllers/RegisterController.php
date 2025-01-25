<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Classes\ApiResponseClass;
use App\Http\Requests\StoreRegisterRequest;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function index(StoreRegisterRequest $request)
    {
        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => $request->input('is_admin', 1),
            'status' => $request->input('status', 'Active'),
        ];

        DB::beginTransaction();
        try {
            $user = User::create($details);
            DB::commit();
            return ApiResponseClass::sendResponse(
                $user,
                'User registration successful!',
                201
            );
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }
}