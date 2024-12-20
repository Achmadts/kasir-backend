<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'kode_produk' => $this->kode_produk,
            'nama_produk' => $this->nama_produk,
            'harga_beli' => $this->harga_beli,
            'harga_jual' => $this->harga_jual,
            'stok' => $this->stok,
            'deskripsi' => $this->deskripsi,
            'foto' => $this->foto ? url('storage/' . $this->foto) : null,
        ];
    }
}
