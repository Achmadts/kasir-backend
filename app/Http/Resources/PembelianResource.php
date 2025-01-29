<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PembelianResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_supplier' => $this->nama_supplier,
            'date' => $this->date,
            'quantity' => $this->quantity,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'total_pembayaran' => $this->total_pembayaran,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'no_rekening_penerima' => $this->no_rekening_penerima,
            'nama_rekening_penerima' => $this->nama_rekening_penerima,
            'bukti_transfer' => $this->bukti_transfer ? url('storage/' . $this->bukti_transfer) : null,
            'note' => $this->note ?? null,
            'detail_pembelians' => DetailPembelianResource::collection($this->detailPembelians),
        ];
    }
}
