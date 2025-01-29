<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\PembelianExport;
use App\Classes\ApiResponseClass;
use Illuminate\Support\Facades\{Storage, DB};
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\PembelianResource;
use App\Interfaces\PembelianRepositoryInterface;
use App\Http\Middleware\{CheckAdmin, CheckJwtToken};
use Illuminate\Routing\Controllers\{Middleware, HasMiddleware};
use App\Http\Requests\{StorePembelianRequest, UpdatePembelianRequest};

class PembelianController extends Controller implements HasMiddleware
{
    private PembelianRepositoryInterface $pembelianRepositoryInterface;

    public static function middleware(): array
    {
        return [
            'auth:api',
            new Middleware(CheckJwtToken::class, only: ['index', 'show', 'store', 'update', 'destroy']), //opsional
            new Middleware(CheckAdmin::class, only: ['update', 'destroy', 'store']), // method yang tidak boleh diakses oleh is_admin === 0
        ];
    }

    public function __construct(PembelianRepositoryInterface $pembelianRepositoryInterface)
    {
        $this->pembelianRepositoryInterface = $pembelianRepositoryInterface;
    }

    public function export()
    {
        return Excel::download(new PembelianExport, 'pembelians.xlsx');
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 200);
        $searchTerm = $request->input('searchTerm', '');
        $pembelian = $this->pembelianRepositoryInterface->index($perPage, $searchTerm);
        return ApiResponseClass::sendResponse(PembelianResource::collection($pembelian)->response()->getData(true), '', 200);
    }

    public function store(StorePembelianRequest $request)
    {
        $details = [
            'date' => $request->date,
            'nama_supplier' => $request->nama_supplier,
            'tax' => $request->tax,
            'discount' => $request->discount,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'total_pembayaran' => $request->total_pembayaran,
            'note' => $request->note,
            'quantity' => $request->quantity,
        ];

        if ($request->hasFile('bukti_transfer')) {
            $filePath = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $details['bukti_transfer'] = $filePath;
        }

        if ($request->payment_method === 'Bank Transfer') {
            $details['no_rekening_penerima'] = $request->no_rekening_penerima;
            $details['nama_rekening_penerima'] = $request->nama_rekening_penerima;
        }

        DB::beginTransaction();
        try {
            $pembelian = $this->pembelianRepositoryInterface->store($details);
            $idProduks = $request->id_produk;
            $jumlahProduks = $request->jumlah_produk;
            $subTotals = $request->sub_total;
            foreach ($idProduks as $index => $idProduk) {
                $stokIncrement = $jumlahProduks[$index] ?? 0;
                DB::table('produks')->where('id', $idProduk)->increment('stok', $stokIncrement);
                DB::table('detail_pembelians')->insert([
                    'id_pembelian' => $pembelian->id,
                    'id_produk' => $idProduk,
                    'jumlah_produk' => $stokIncrement,
                    'sub_total' => $subTotals[$index] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return ApiResponseClass::sendResponse(new PembelianResource($pembelian), 'Pembelian Created Successfully', 201);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }

    public function show($id)
    {
        $pembelian = $this->pembelianRepositoryInterface->getById($id);

        if (!$pembelian) {
            return ApiResponseClass::sendError('Pembelian Not Found', 404);
        }

        return APiResponseClass::sendResponse(new PembelianResource($pembelian), '', 200);
    }

    public function update(UpdatePembelianRequest $request, $id)
    {
        $pembelian = $this->pembelianRepositoryInterface->getById($id);

        if (!$pembelian) {
            return ApiResponseClass::sendError('Pembelian not found', 404);
        }

        $updateDetails = [
            'date' => $request->date ?? $pembelian->date,
            'nama_supplier' => $request->nama_supplier ?? $pembelian->nama_supplier,
            'tax' => $request->tax ?? $pembelian->tax,
            'discount' => $request->discount ?? $pembelian->discount,
            'status' => $request->status ?? $pembelian->status,
            'payment_method' => $request->payment_method ?? $pembelian->payment_method,
            'total_pembayaran' => $request->total_pembayaran ?? $pembelian->total_pembayaran,
            'note' => $request->note ?? $pembelian->note,
            'quantity' => $request->quantity ?? $pembelian->quantity,
        ];

        if ($request->hasFile('bukti_transfer')) {
            if ($pembelian->bukti_transfer && Storage::disk('public')->exists($pembelian->bukti_transfer)) {
                Storage::disk('public')->delete($pembelian->bukti_transfer);
            }
            $filePath = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $updateDetails['bukti_transfer'] = $filePath;
        }

        if (($request->payment_method ?? $pembelian->payment_method) === 'Bank Transfer') {
            $updateDetails['no_rekening_penerima'] = $request->no_rekening_penerima ?? $pembelian->no_rekening_penerima;
            $updateDetails['nama_rekening_penerima'] = $request->nama_rekening_penerima ?? $pembelian->nama_rekening_penerima;
        }

        DB::beginTransaction();
        try {
            $this->pembelianRepositoryInterface->update($updateDetails, $id);
            $idProduks = $request->id_produk;
            $jumlahProduks = $request->jumlah_produk;
            $subTotals = $request->sub_total;
            $currentDetails = DB::table('detail_pembelians')->where('id_pembelian', $id)->get();
            foreach ($currentDetails as $detail) {
                DB::table('produks')->where('id', $detail->id_produk)->decrement('stok', $detail->jumlah_produk);
            }

            DB::table('detail_pembelians')->where('id_pembelian', $id)->delete();
            foreach ($idProduks as $index => $idProduk) {
                $stokIncrement = $jumlahProduks[$index] ?? 0;
                DB::table('produks')->where('id', $idProduk)->increment('stok', $stokIncrement);
                DB::table('detail_pembelians')->insert([
                    'id_pembelian' => $id,
                    'id_produk' => $idProduk,
                    'jumlah_produk' => $stokIncrement,
                    'sub_total' => $subTotals[$index] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
            return ApiResponseClass::sendResponse('Pembelian Update Successful', '', 201);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex);
        }
    }
    public function destroy($id)
    {
        $pembelian = $this->pembelianRepositoryInterface->getById($id);

        if (!$pembelian) {
            return ApiResponseClass::sendError('Pembelian Not Found', 404);
        }

        $path = $pembelian->bukti_transfer;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->pembelianRepositoryInterface->delete($id);
        return ApiResponseClass::sendResponse('Pembelian Delete Successful', 204);
    }
}
