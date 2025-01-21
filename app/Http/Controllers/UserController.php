<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Exports\CashierExport;
use App\Classes\ApiResponseClass;
use App\Http\Resources\UserResource;
use Maatwebsite\Excel\Facades\Excel;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Support\Facades\{DB, Storage, Auth, Hash};
use App\Http\Requests\{StoreUserRequest, UpdateUserRequest};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};

class UserController extends Controller implements HasMiddleware
{
    private UserRepositoryInterface $userRepositoryInterface;
    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['index', 'show', 'store', 'update', 'destroy']), //opsional
            new Middleware(CheckAdmin::class, only: ['destroy', 'store', "index"]), // method yang tidak boleh diakses oleh is_admin === 0
        ];
    }

    public function __construct(UserRepositoryInterface $userRepositoryInterface)
    {
        $this->userRepositoryInterface = $userRepositoryInterface;
    }

    public function export()
    {
        return Excel::download(new CashierExport, 'cashiers.xlsx');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 200);
        $searchTerm = $request->input('searchTerm', '');
        $user = $this->userRepositoryInterface->index($perPage, $searchTerm);
        return ApiResponseClass::sendResponse(UserResource::collection($user)->response()->getData(true), '', 200);
    }

    public function store(StoreUserRequest $request)
    {
        $imagePath = null;
        if ($request->hasFile('images')) {
            $imagePath = $request->file('images')->store('images', 'public');
        }

        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'is_admin' => $request->is_admin,
            'password' => bcrypt($request->password),
            'images' => $imagePath,
            'status' => $request->status,
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

    public function show($id)
    {
        $user = $this->userRepositoryInterface->getById($id);

        if (!$user) {
            return ApiResponseClass::sendError('User Not Found', 404);
        }

        $user = $this->userRepositoryInterface->getById($id);
        return ApiResponseClass::sendResponse(new UserResource($user), '', 200);
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $loggedInUser = Auth::user();
        $user = $this->userRepositoryInterface->getById($id);

        if (!$user) {
            return ApiResponseClass::sendError('User Not Found', 404);
        }

        if ($loggedInUser->id !== $user->id && $loggedInUser->is_admin !== 1) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $newEmail = $request->email ?? $user->email;
        $existingUserWithEmail = User::where('email', $newEmail)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUserWithEmail) {
            return ApiResponseClass::sendError('The Email has already been taken by another user.', 422);
        }

        $newName = $request->name ?? $user->name;
        $existingUserWithName = User::where('name', $newName)
            ->where('id', '!=', $id)
            ->first();

        if ($existingUserWithName) {
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
            'is_admin' => $request->is_admin ?? $user->is_admin,
            'password' => $request->password ? bcrypt($request->password) : $user->password,
            'images' => $oldImagePath,
            'status' => $request->status ?? $user->status,
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

    public function destroy($id)
    {
        $user = $this->userRepositoryInterface->getById($id);
        $loggedInUser = Auth::user();

        if (!$loggedInUser) {
            return ApiResponseClass::sendError('Unauthorized Access', 401);
        }

        if ($loggedInUser->id === $user->id) {
            return ApiResponseClass::sendError('You cannot delete your own account.', 403);
        }

        if ($loggedInUser->is_admin !== 1) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $this->userRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('User Delete Successful', 204);
    }
}
