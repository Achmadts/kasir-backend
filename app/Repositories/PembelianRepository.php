<?php

namespace App\Repositories;

use App\Models\Pembelian;
use App\Interfaces\PembelianRepositoryInterface;

class PembelianRepository implements PembelianRepositoryInterface
{
    public function index($perPage, $searchTerm = '')
    {
        $query = Pembelian::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_supplier', 'like', '%' . $searchTerm . '%')
                    ->orWhere('id_produk', 'like', '%' . $searchTerm . '%')
                    ->orWhere('date', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function getById($id)
    {
        return Pembelian::find($id);
    }

    public function store(array $data)
    {
        return Pembelian::create($data);
    }

    public function update(array $data, $id)
    {
        return Pembelian::whereId($id)->update($data);
    }

    public function delete($id)
    {
        Pembelian::destroy($id);
    }
}
