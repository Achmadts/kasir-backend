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

    public function getSalesPurchases(Request $request)
    {
        $days = $request->query('days');

        if (!is_numeric($days)) {
            return response()->json(['error' => 'Invalid days parameter'], 400);
        }

        if ($days == 0) {
            $groupingFormatSales = 'YEAR(tanggal_penjualan)';
            $groupingFormatPurchases = 'YEAR(date)';

            $salesQuery = DB::table('penjualans')
                ->select(
                    DB::raw('YEAR(tanggal_penjualan) as period'),
                    DB::raw('SUM(total_harga) as total_sales'),
                    DB::raw('SUM(quantity) as total_sales_quantity')
                )
                ->groupBy(DB::raw('YEAR(tanggal_penjualan)'))
                ->orderBy('period', 'asc');

            $purchaseQuery = DB::table('pembelians')
                ->select(
                    DB::raw('YEAR(date) as period'),
                    DB::raw('SUM(total_pembayaran) as total_purchases'),
                    DB::raw('SUM(jumlah_barang) as total_purchases_quantity')
                )
                ->groupBy(DB::raw('YEAR(date)'))
                ->orderBy('period', 'asc');
        } else {
            $groupingFormatSales = $days >= 180 ? 'YEAR(tanggal_penjualan), MONTH(tanggal_penjualan)' : 'DATE(tanggal_penjualan)';
            $groupingFormatPurchases = $days >= 180 ? 'YEAR(date), MONTH(date)' : 'DATE(date)';
            $startDate = now()->subDays($days)->startOfDay();
            $endDate = now()->endOfDay();

            $salesQuery = DB::table('penjualans')
                ->select(
                    DB::raw($days >= 180 ? 'DATE_FORMAT(tanggal_penjualan, "%Y-%m") as period' : 'DATE(tanggal_penjualan) as period'),
                    DB::raw('SUM(total_harga) as total_sales'),
                    DB::raw('SUM(quantity) as total_sales_quantity')
                )
                ->whereBetween('tanggal_penjualan', [$startDate, $endDate])
                ->groupBy(DB::raw($days >= 180 ? 'DATE_FORMAT(tanggal_penjualan, "%Y-%m")' : 'DATE(tanggal_penjualan)'))
                ->orderBy('period', 'asc');

            $purchaseQuery = DB::table('pembelians')
                ->select(
                    DB::raw($days >= 180 ? 'DATE_FORMAT(date, "%Y-%m") as period' : 'DATE(date) as period'),
                    DB::raw('SUM(total_pembayaran) as total_purchases'),
                    DB::raw('SUM(jumlah_barang) as total_purchases_quantity')
                )
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy(DB::raw($days >= 180 ? 'DATE_FORMAT(date, "%Y-%m")' : 'DATE(date)'))
                ->orderBy('period', 'asc');
        }

        $salesData = $salesQuery->get()->map(function ($sale) {
            return [
                'period' => $sale->period,
                'total_sales' => (string) $sale->total_sales,
                'total_sales_quantity' => (string) $sale->total_sales_quantity,
            ];
        });

        $purchaseData = $purchaseQuery->get()->map(function ($purchase) {
            return [
                'period' => $purchase->period,
                'total_purchases' => (string) $purchase->total_purchases,
                'total_purchases_quantity' => (string) $purchase->total_purchases_quantity,
            ];
        });

        return response()->json([
            'sales' => $salesData,
            'purchases' => $purchaseData,
        ]);
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
