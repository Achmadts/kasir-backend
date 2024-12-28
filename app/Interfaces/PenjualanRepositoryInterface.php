<?php

namespace App\Interfaces;

interface PenjualanRepositoryInterface
{
    public function index($perPage, $searchTerm);
    public function getById($id);
    public function store(array $data);
    public function update(array $data, $id);
    public function delete($id);
}
