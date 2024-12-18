<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ProdukExport;
use App\Classes\ApiResponseClass;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\ProductResource;
use App\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\{DB, Auth, Storage};
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};
use App\Http\Requests\{StoreProdukRequest, UpdateProdukRequest};

class ProdukController extends Controller implements HasMiddleware
{
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

    public function store(StoreProdukRequest $request)
    {
        $loggedInUser = Auth::user();

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        if ($request->harga_jual <= $request->harga_beli) {
            return ApiResponseClass::sendError('Harga jual tidak boleh kurang dari atau sama dengan harga beli.', 422);
        }

        $details = [
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'harga_beli' => $request->harga_beli,
            'harga_jual' => $request->harga_jual,
            'stok' => $request->stok,
            'deskripsi' => $request->deskripsi,
            'kategori_id' => $request->kategori_id,
        ];

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk', 'public');
            $details['foto'] = $fotoPath;
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepositoryInterface->store($details);

            DB::commit();
            return ApiResponseClass::sendResponse(
                new ProductResource($product),
                'Product Create Successful',
                201
            );
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }

    public function show($id)
    {
        $product = $this->productRepositoryInterface->getById($id);

        if (!$product) {
            return ApiResponseClass::sendError('Product Not Found', 404);
        }

        return ApiResponseClass::sendResponse(new ProductResource($product), '', 200);
    }

    public function update(UpdateProdukRequest $request, $id)
    {
        $loggedInUser = Auth::user();
        $product = $this->productRepositoryInterface->getById($id);

        if (!$product || !is_object($product)) {
            return ApiResponseClass::sendError('Produk Not Found', 404);
        }

        if (!$loggedInUser->is_admin) {
            return ApiResponseClass::sendError('Unauthorized Access', 403);
        }

        $updateDetails = [
            'kode_produk' => $request->kode_produk ?? $product->kode_produk,
            'nama_produk' => $request->nama_produk ?? $product->nama_produk,
            'harga_beli' => $request->harga_beli ?? $product->harga_beli,
            'harga_jual' => $request->harga_jual ?? $product->harga_jual,
            'stok' => $request->stok ?? $product->stok,
            'deskripsi' => $request->deskripsi ?? $product->deskripsi,
            'kategori_id' => $request->kategori_id ?? $product->kategori_id,
        ];

        if ($request->hasFile('foto')) {
            if ($product->foto && Storage::disk('public')->exists($product->foto)) {
                Storage::disk('public')->delete($product->foto);
            }
            $fotoPath = $request->file('foto')->store('produk', 'public');
            $updateDetails['foto'] = $fotoPath;
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepositoryInterface->update($updateDetails, $id);

            DB::commit();
            return ApiResponseClass::sendResponse('Product Update Successful', 200);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }

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

        $path = $product->foto;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->productRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('Product Delete Successful', 204);
    }
}