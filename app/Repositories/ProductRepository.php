<?php

namespace App\Repositories;

use App\Interfaces\ProductRepositoryInterface;
use App\Models\Produk;

class ProductRepository implements ProductRepositoryInterface
{
    public function index()
    {
        return Produk::all();
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
