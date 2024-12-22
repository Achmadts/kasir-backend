<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Produk;

class ProductRepository implements ProductRepositoryInterface
{
    public function index($perPage, $searchTerm = '')
    {
        $query = Produk::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', '%' . $searchTerm . '%')
                    ->orWhere('kode_produk', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function getById($id)
    {
        return Produk::find($id);
    }

    public function store(array $data)
    {
        return Produk::create($data);
    }

    public function update(array $data, $id)
    {
        return Produk::whereId($id)->update($data);
    }

    public function delete($id)
    {
        Produk::destroy($id);
    }
}
