<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPenjualanResource extends JsonResource
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
            'nama_produk' => $this->produk->nama_produk ?? null,
            'jumlah_produk' => $this->jumlah_produk,
            'sub_total' => $this->sub_total,
        ];
    }
}