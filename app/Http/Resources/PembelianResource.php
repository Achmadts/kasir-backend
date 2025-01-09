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
            'id_produk' => $this->id_produk,
            'date' => $this->date,
            'nama_supplier' => $this->nama_supplier,
            'tax' => $this->tax,
            'discount' => $this->discount,
            'jumlah_barang' => $this->jumlah_barang,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'total_pembayaran' => $this->total_pembayaran,
            'note' => $this->note
        ];
    }
}
