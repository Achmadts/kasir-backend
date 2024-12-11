<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Classes\ApiResponseClass;
use App\Http\Resources\UserResource;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Support\Facades\{DB, Storage, Auth, Hash};
use App\Http\Requests\{StoreUserRequest, UpdateUserRequest};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};

class UserController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    private UserRepositoryInterface $userRepositoryInterface;

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['index']),
            new Middleware(CheckAdmin::class, except: ['store', 'show', 'update', 'destroy'])
        ];
    }

    public function __construct(UserRepositoryInterface $userRepositoryInterface)
    {
        $this->userRepositoryInterface = $userRepositoryInterface;
    }
    public function index()
    {
        $user = $this->userRepositoryInterface->index();
        return ApiResponseClass::sendResponse(UserResource::collection($user), '', 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('images')) {
            $imagePath = $request->file('images')->store('images', 'public');
        }

        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'images' => $imagePath,
        ];

        DB::beginTransaction();
        try {
            $user = $this->userRepositoryInterface->store($details);

            DB::commit();
            return ApiResponseClass::sendResponse(new UserResource($user), 'User  Create Successful', 201);

        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = $this->userRepositoryInterface->getById($id);

        if (!$user) {
            return ApiResponseClass::sendError('User Not Found', 404);
        }

        $user = $this->userRepositoryInterface->getById($id);
        return ApiResponseClass::sendResponse(new UserResource($user), '', 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $loggedInUser = Auth::user();
        $user = $this->userRepositoryInterface->getById($id);

        if (!$user) {
            return ApiResponseClass::sendError('User  Not Found', 404);
        }
        if ($loggedInUser->id !== $user->id && !$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $newEmail = $request->email ?? $user->email;
        $existingUserWithEmail = User::where('email', $newEmail)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUserWithEmail && $existingUserWithEmail->id !== $id) {
            return ApiResponseClass::sendError('The Email has already been taken by another user.', 422);
        }

        $newName = $request->name ?? $user->name;
        $existingUserWithName = User::where('name', $newName)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUserWithName && $existingUserWithName->id !== $id) {
            return ApiResponseClass::sendError('The Name has already been taken by another user.', 422);
        }

        if ($request->has('currentPassword')) {
            if (!Hash::check($request->currentPassword, $user->password)) {
                return ApiResponseClass::sendError('The current password is incorrect.', 422);
            }
        }

        $oldImagePath = $user->images;
        $updateDetails = [
            'name' => $request->name ?? $user->name,
            'email' => $newEmail ?? $user->email,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'images' => $oldImagePath,
        ];

        DB::beginTransaction();
        try {
            if ($request->hasFile('images')) {
                $newImagePath = $request->file('images')->store('images', 'public');

                if ($oldImagePath) {
                    Storage::disk('public')->delete($oldImagePath);
                }
                $updateDetails['images'] = $newImagePath;
            }

            $this->userRepositoryInterface->update($updateDetails, $id);

            DB::commit();
            return ApiResponseClass::sendResponse('User Update Successful', '', 200);

        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->userRepositoryInterface->getById($id);
        $loggedInUserId = Auth::id();

        if (!$user || $user->id !== $loggedInUserId) {
            return ApiResponseClass::sendError('User Not Found', 404);
        }
        $this->userRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('User Delete Successful', '', 204);
    }
}
