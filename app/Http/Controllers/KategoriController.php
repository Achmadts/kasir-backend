<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Exports\KategoriExport;
use App\Classes\ApiResponseClass;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\KategoriResource;
use Illuminate\Support\Facades\{DB, Auth};
use App\Interfaces\KategoriRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
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
            new Middleware(CheckJwtToken::class, only: ['index', 'show', 'store', 'update', 'destroy']), //opsional
            new Middleware(CheckAdmin::class, only: ['destroy', 'store']), // method yang tidak boleh diakses oleh is_admin === 0
        ];
    }

    public function __construct(KategoriRepositoryInterface $kategoriRepositoryInterface)
    {
        $this->kategoriRepositoryInterface = $kategoriRepositoryInterface;
    }

    public function export()
    {
        return Excel::download(new KategoriExport, 'categories.xlsx');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 200);
        $searchTerm = $request->input('searchTerm', '');
        $kategori = $this->kategoriRepositoryInterface->index($perPage, $searchTerm);

        return ApiResponseClass::sendResponse(KategoriResource::collection($kategori)->response()->getData(true), '', 200);
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
        $kategori = $this->kategoriRepositoryInterface->getById($id);

        if (!$kategori) {
            return ApiResponseClass::sendError('Kategori Not Found', 404);
        }

        $NewKodeKategori = $request->kode_kategori ?? $kategori->kode_kategori;
        $existingKategoriWithKodeKategori = Kategori::where('kode_kategori', $NewKodeKategori)
            ->where('id', '!=', $id)
            ->first();

        if ($existingKategoriWithKodeKategori && $existingKategoriWithKodeKategori->id !== $id) {
            return ApiResponseClass::sendError('The Category Code has already been taken.', 422);
        }

        $NewNamaKategori = $request->nama_kategori ?? $kategori->nama_kategori;
        $existingKategoriWithNamaKategori = Kategori::where('nama_kategori', $NewNamaKategori)
            ->where('id', '!=', $id)
            ->first();

        if ($existingKategoriWithNamaKategori && $existingKategoriWithNamaKategori->id !== $id) {
            return ApiResponseClass::sendError('The Category Name has already been taken.', 422);
        }

        $updateDetails = [
            'kode_kategori' => $request->kode_kategori ?? $kategori->kode_kategori,
            'nama_kategori' => $request->nama_kategori ?? $kategori->nama_kategori,
        ];

        DB::beginTransaction();
        try {
            $kategori = $this->kategoriRepositoryInterface->update($updateDetails, $id);

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
        $kategori = $this->kategoriRepositoryInterface->getById($id);

        if (!$kategori) {
            return ApiResponseClass::sendError('Kategori Not Found', 404);
        }

        $this->kategoriRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('Kategori Delete Successful', 204);
    }
}
