<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Classes\ApiResponseClass;
use App\Http\Resources\KategoriResource;
use Illuminate\Support\Facades\{DB, Auth};
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use App\Interfaces\Interfaces\KategoriRepositoryInterface;
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};
use App\Http\Requests\{StoreKategoriRequest, UpdateKategoriRequest};

class KategoriController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    private KategoriRepositoryInterface $kategoriRepositoryInterface;

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['index', 'update', 'destroy', 'store']),
            new Middleware(CheckAdmin::class, except: ['show', 'index'])
        ];
    }

    public function __construct(KategoriRepositoryInterface $kategoriRepositoryInterface)
    {
        $this->kategoriRepositoryInterface = $kategoriRepositoryInterface;
    }

    public function index()
    {
        $kategori = $this->kategoriRepositoryInterface->index();
        return ApiResponseClass::sendResponse(KategoriResource::collection($kategori), '', 200);
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
    public function store(StoreKategoriRequest $request)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $details = [
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
        ];
        DB::beginTransaction();
        try {
            $kategori = $this->kategoriRepositoryInterface->store($details);

            DB::commit();
            return ApiResponseClass::sendResponse(new KategoriResource($kategori), 'Kategori Create Successful', 201);

        } catch (\Exception $ex) {
            return ApiResponseClass::rollback($ex);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kategori = $this->kategoriRepositoryInterface->getById($id);

        if (!$kategori) {
            return ApiResponseClass::sendError('Kategori Not Found', 404);
        }

        return ApiResponseClass::sendResponse(new KategoriResource($kategori), '', 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kategori $kategori)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKategoriRequest $request, $id)
    {
        $loggedInUser = Auth::user();
        $kategori = $this->kategoriRepositoryInterface->getById($id);

        if (!$kategori) {
            return ApiResponseClass::sendError('Kategori Not Found', 404);
        }

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $updateDetails = [
            'kode_kategori' => $request->kode_kategori,
            'nama_kategori' => $request->nama_kategori,
        ];

        DB::beginTransaction();
        try {
            $product = $this->kategoriRepositoryInterface->update($updateDetails, $id);

            DB::commit();
            return ApiResponseClass::sendResponse('Kategori Update Successful', '', 201);

        } catch (\Exception $ex) {
            return ApiResponseClass::rollback($ex);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $loggedInUser = Auth::user();
        $kategori = $this->kategoriRepositoryInterface->getById($id);

        if (!$kategori) {
            return ApiResponseClass::sendError('Kategori Not Found', 404);
        }

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $this->kategoriRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('Kategori Delete Successful', 204);
    }
}
