<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\DetailPenjualanResource;

class PenjualanResource extends JsonResource
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
            'nama_pelanggan' => $this->pelanggan->nama_pelanggan ?? null,
            'tanggal_penjualan' => $this->tanggal_penjualan,
            'quantity' => $this->quantity,
            'pajak' => $this->pajak,
            'diskon' => $this->diskon,
            'total_harga' => $this->total_harga,
            'status' => $this->status,
            'metode_pembayaran' => $this->metode_pembayaran,
            'catatan' => $this->catatan ?? null,
            'detail_penjualans' => DetailPenjualanResource::collection($this->detailPenjualans),
        ];
    }
}