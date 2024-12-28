<?php

namespace App\Repositories;

use App\Models\Penjualan;
use App\Interfaces\PenjualanRepositoryInterface;

class PenjualanRepository implements PenjualanRepositoryInterface
{
    public function index($perPage, $searchTerm = '')
    {
        $query = Penjualan::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('pelanggan', function ($pelangganQuery) use ($searchTerm) {
                    $pelangganQuery->where('nama_pelanggan', 'like', '%' . $searchTerm . '%');
                })
                    ->orWhere('tanggal_penjualan', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->with('pelanggan')->paginate($perPage);
    }

    public function getById($id)
    {
        return Penjualan::find($id);
    }

    public function store(array $data)
    {
        return Penjualan::create($data);
    }

    public function update(array $data, $id)
    {
        return Penjualan::whereId($id)->update($data);
    }

    public function delete($id)
    {
        Penjualan::destroy($id);
    }
}
