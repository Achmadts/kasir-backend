<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Classes\ApiResponseClass;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PenjualanResource;
use App\Interfaces\PenjualanRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};
use App\Http\Requests\{StorePenjualanRequest, UpdatePenjualanRequest};

class PenjualanController extends Controller implements HasMiddleware
{
    private PenjualanRepositoryInterface $penjualanRepositoryInterface;

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['index', 'show', 'store', 'destroy']), //opsional
            new Middleware(CheckAdmin::class, only: ['update', 'destroy']), // method yang tidak boleh diakses oleh is_admin === 0
        ];
    }

    public function __construct(PenjualanRepositoryInterface $penjualanRepositoryInterface)
    {
        $this->penjualanRepositoryInterface = $penjualanRepositoryInterface;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 200);
        $searchTerm = $request->input('searchTerm', '');
        $penjualan = $this->penjualanRepositoryInterface->index($perPage, $searchTerm);
        return ApiResponseClass::sendResponse(PenjualanResource::collection($penjualan)->response()->getData(true), '', 200);
    }

    public function store(StorePenjualanRequest $request)
    {
        DB::beginTransaction();
        try {
            $pelanggan = DB::table('pelanggans')->insertGetId([
                'nama_pelanggan' => $request->nama_pelanggan,
                'kota' => "Karawang",
                'negara' => "Indonesia",
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $penjualan = DB::table('penjualans')->insertGetId([
                'tanggal_penjualan' => $request->tanggal_penjualan,
                'quantity' => $request->quantity,
                'pajak' => $request->pajak,
                'diskon' => $request->diskon,
                'total_harga' => $request->total_harga,
                'status' => $request->status,
                'metode_pembayaran' => $request->metode_pembayaran,
                'catatan' => $request->catatan,
                'id_pelanggan' => $pelanggan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detailPenjualans = [];
            foreach ($request->id_produk as $index => $id_produk) {
                $detailPenjualans[] = [
                    'id_penjualan' => $penjualan,
                    'id_produk' => $id_produk,
                    'jumlah_produk' => $request->jumlah_produk[$index],
                    'sub_total' => $request->sub_total[$index],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('detail_penjualans')->insert($detailPenjualans);
            DB::commit();

            $penjualanResource = new PenjualanResource(Penjualan::with(['pelanggan', 'detailPenjualans.produk'])->find($penjualan));
            return ApiResponseClass::sendResponse($penjualanResource, 'Penjualan Create Successful', 201);

        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::sendError($ex->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $penjualan = $this->penjualanRepositoryInterface->getById($id);

        if (!$penjualan) {
            return ApiResponseClass::sendError('Penjualan Not Found', 404);
        }

        return ApiResponseClass::sendResponse(new PenjualanResource($penjualan), '', 200);
    }

    public function update(UpdatePenjualanRequest $request, $id)
    {
        $penjualan = Penjualan::with('pelanggan', 'detailPenjualans')->find($id);

        if (!$penjualan) {
            return ApiResponseClass::sendError('Penjualan Not Found', 404);
        }

        DB::beginTransaction();
        try {
            if ($request->nama_pelanggan) {
                $penjualan->pelanggan->update([
                    'nama_pelanggan' => $request->nama_pelanggan ?? $penjualan->pelanggan->nama_pelanggan,
                    'kota' => "Karawang",
                    'negara' => "Indonesia",
                    'updated_at' => now(),
                ]);
            }

            $penjualan->update([
                'tanggal_penjualan' => $request->tanggal_penjualan ?? $penjualan->tanggal_penjualan,
                'quantity' => $request->quantity ?? $penjualan->quantity,
                'pajak' => $request->pajak ?? $penjualan->pajak,
                'diskon' => $request->diskon ?? $penjualan->diskon,
                'total_harga' => $request->total_harga ?? $penjualan->total_harga,
                'status' => $request->status ?? $penjualan->status,
                'metode_pembayaran' => $request->metode_pembayaran ?? $penjualan->metode_pembayaran,
                'catatan' => $request->catatan ?? $penjualan->catatan,
                'updated_at' => now(),
            ]);

            if (is_array($request->id_produk ?? []) && !empty($request->id_produk)) {
                $penjualan->detailPenjualans()->delete();

                $detailPenjualans = [];
                foreach (($request->id_produk ?? []) as $index => $id_produk) {
                    $existingDetail = $penjualan->detailPenjualans->where('id_produk', $id_produk)->first();

                    $detailPenjualans[] = [
                        'id_penjualan' => $penjualan->id
                            ?? $existingDetail->id_penjualan
                            ?? null,
                        'id_produk' => $id_produk
                            ?? $existingDetail->id_produk
                            ?? null,
                        'jumlah_produk' => $request->jumlah_produk[$index]
                            ?? $existingDetail->jumlah_produk
                            ?? 0,
                        'sub_total' => $request->sub_total[$index]
                            ?? $existingDetail->sub_total
                            ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('detail_penjualans')->insert($detailPenjualans);
            }

            DB::commit();
            $penjualanResource = new PenjualanResource(Penjualan::with(['pelanggan', 'detailPenjualans.produk'])->find($penjualan->id));
            return ApiResponseClass::sendResponse($penjualanResource, 'Penjualan Update Successful', 201);

        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::sendError($ex->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        $penjualan = $this->penjualanRepositoryInterface->getById($id);

        if (!$penjualan) {
            return ApiResponseClass::sendError('Penjualan Not Found', 404);
        }

        DB::beginTransaction();
        try {
            $this->penjualanRepositoryInterface->delete($id);
            $isPelangganUsedElsewhere = Penjualan::where('id_pelanggan', $penjualan->id_pelanggan)->exists();

            if (!$isPelangganUsedElsewhere) {
                DB::table('pelanggans')->where('id', $penjualan->id_pelanggan)->delete();
            }

            DB::commit();
            return ApiResponseClass::sendResponse('Penjualan and related Pelanggan deleted successfully', 204);

        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::sendError($ex->getMessage(), 500);
        }
    }
}
