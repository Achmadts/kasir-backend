<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function index($perPage, $searchTerm = '')
    {
        $query = User::query();

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('is_admin', 'like', '%' . $searchTerm . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function getById($id)
    {
        return User::find($id);
    }

    public function store(array $data)
    {
        return User::create($data);
    }

    public function update(array $data, $id)
    {
        return User::whereId($id)->update($data);
    }

    public function delete($id)
    {
        User::destroy($id);
    }
}
