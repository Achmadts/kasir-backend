<?php

namespace App\Repositories;

use App\Interfaces\KategoriRepositoryInterface;
use App\Models\Kategori;

class KategoriRepository implements KategoriRepositoryInterface
{
    public function index($perPage, $searchTerm = '')
    {
        $query = Kategori::query();
        if ($searchTerm) {
            $query->where('nama_kategori', 'like', '%' . $searchTerm . '%');
        }
        return $query->paginate($perPage);
    }

    public function getById($id)
    {
        return Kategori::find($id);
    }

    public function store(array $data)
    {
        return Kategori::create($data);
    }

    public function update(array $data, $id)
    {
        return Kategori::whereId($id)->update($data);
    }

    public function delete($id)
    {
        Kategori::destroy($id);
    }
}
