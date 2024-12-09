<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use App\Exports\ProdukExport;
use App\Classes\ApiResponseClass;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\{DB, Auth};
use App\Interfaces\ProductRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};
use App\Http\Requests\{StoreProdukRequest, UpdateProdukRequest};

class ProdukController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    private ProductRepositoryInterface $productRepositoryInterface;

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['store', 'update', 'destroy']),
            new Middleware(CheckAdmin::class, except: ['index', 'show'])
        ];
    }

    public function __construct(ProductRepositoryInterface $productRepositoryInterface)
    {
        $this->productRepositoryInterface = $productRepositoryInterface;
    }

    public function export()
    {
        return Excel::download(new ProdukExport, 'products.xlsx');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 200);
        $searchTerm = $request->input('searchTerm', '');
        $produk = $this->productRepositoryInterface->index($perPage, $searchTerm);
        return ApiResponseClass::sendResponse(ProductResource::collection($produk)->response()->getData(true), '', 200);
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
    public function store(StoreProdukRequest $request)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $details = [
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok
        ];

        DB::beginTransaction();
        try {
            $product = $this->productRepositoryInterface->store($details);

            DB::commit();
            return ApiResponseClass::sendResponse(new ProductResource($product), 'Product Create Successful', 201);

        } catch (\Exception $ex) {
            return ApiResponseClass::rollback($ex);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = $this->productRepositoryInterface->getById($id);

        if (!$product) {
            return ApiResponseClass::sendError('Product Not Found', 404);
        }

        return ApiResponseClass::sendResponse(new ProductResource($product), '', 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produk $produk)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProdukRequest $request, $id)
    {
        $loggedInUser = Auth::user();
        $product = $this->productRepositoryInterface->getById($id);

        if (!$product) {
            return ApiResponseClass::sendError('Produk Not Found', 404);
        }

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }
        
        $updateDetails = [
            'nama_produk' => $request->nama_produk ?? $product->nama_produk,
            'harga' => $request->harga ?? $product->harga,
            'stok' => $request->stok ?? $product->stok
        ];
        DB::beginTransaction();
        try {
            $product = $this->productRepositoryInterface->update($updateDetails, $id);

            DB::commit();
            return ApiResponseClass::sendResponse('Product Update Successful', '', 201);

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
        $product = $this->productRepositoryInterface->getById($id);

        if (!$product) {
            return ApiResponseClass::sendError('Product Not Found', 404);
        }

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $this->productRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('Product Delete Successful', 204);

    }
}
