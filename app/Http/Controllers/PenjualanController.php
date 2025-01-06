<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Exports\PenjualanExport;
use App\Classes\ApiResponseClass;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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

    public function export()
    {
        return Excel::download(new PenjualanExport, 'penjualans.xlsx');
    }

    public function getSalesPurchases()
    {
        $data = DB::table('penjualans')
            ->select(
                DB::raw('DATE(tanggal_penjualan) as date'),
                DB::raw('SUM(total_harga) as sales'),
                DB::raw('SUM(quantity) as sales') // purchases ini nantinya harus diganti jadi sales setelah ada purchases controller
            )
            ->where('tanggal_penjualan', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
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
                $jumlah_produk = $request->jumlah_produk[$index];
                $product = DB::table('produks')->where('id', $id_produk)->first();

                if (!$product) {
                    return ApiResponseClass::sendError("Product not found!", 404);
                }

                if ($product->stok < $jumlah_produk) {
                    return ApiResponseClass::sendError("Stok produk {$product->nama_produk} tidak mencukupi!", 422);
                }

                DB::table('produks')
                    ->where('id', $id_produk)
                    ->decrement('stok', $jumlah_produk);

                $detailPenjualans[] = [
                    'id_penjualan' => $penjualan,
                    'id_produk' => $id_produk,
                    'jumlah_produk' => $jumlah_produk,
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
        $penjualan = Penjualan::with('pelanggan', 'detailPenjualans.produk')->find($id);

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

            foreach ($penjualan->detailPenjualans as $detail) {
                $product = $detail->produk;
                if ($product) {
                    $product->increment('stok', $detail->jumlah_produk);
                }
            }

            $penjualan->detailPenjualans()->delete();
            $detailPenjualans = [];
            foreach (($request->id_produk ?? []) as $index => $id_produk) {
                $jumlah_produk = $request->jumlah_produk[$index] ?? 0;
                $sub_total = $request->sub_total[$index] ?? 0;
                $product = Produk::find($id_produk);
                if ($product) {
                    if ($jumlah_produk > 0) {
                        if ($product->stok < $jumlah_produk) {
                            return ApiResponseClass::sendError("Stok produk {$product->nama_produk} tidak cukup! Qty maksimal {$product->stok}", 422);
                        }

                        $product->decrement('stok', $jumlah_produk);
                    }
                }

                $detailPenjualans[] = [
                    'id_penjualan' => $penjualan->id,
                    'id_produk' => $id_produk,
                    'jumlah_produk' => $jumlah_produk,
                    'sub_total' => $sub_total,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('detail_penjualans')->insert($detailPenjualans);
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
