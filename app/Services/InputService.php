<?php

namespace App\Services;

use App\Models\Input;
use Illuminate\Pagination\Paginator;

class InputService
{
    public function list(int $perPage = 15): Paginator
    {
        return Input::orderBy('dt_buy', 'desc')->paginate($perPage);
    }

    public function findById(int $id): Input
    {
        return Input::findOrFail($id);
    }

    public function create(array $data): Input
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        return Input::create($data);
    }

    public function update(Input $input, array $data): Input
    {
        $input->update($data);

        return $input;
    }

    public function delete(Input $input): bool
    {
        return $input->delete();
    }
}