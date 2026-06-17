<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInputRequest;
use App\Http\Requests\UpdateInputRequest;
use App\Models\Input;
use App\Services\InputService;
use Illuminate\Http\JsonResponse;

class InputController extends Controller
{
    public function __construct(private InputService $inputService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('staff.only')->except('show', 'index');
        $this->middleware('admin.only')->only('destroy');
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Input::class);
        $inputs = $this->inputService->list();

        return response()->json($inputs);
    }

    public function show(Input $input): JsonResponse
    {
        $this->authorize('view', $input);

        return response()->json($input);
    }

    public function store(StoreInputRequest $request): JsonResponse
    {
        $this->authorize('create', Input::class);
        $input = $this->inputService->create($request->validated());

        return response()->json($input, 201);
    }

    public function update(UpdateInputRequest $request, Input $input): JsonResponse
    {
        $this->authorize('update', $input);
        $input = $this->inputService->update($input, $request->validated());

        return response()->json($input);
    }

    public function destroy(Input $input): JsonResponse
    {
        $this->authorize('delete', $input);
        $this->inputService->delete($input);

        return response()->json(['message' => 'Input deleted successfully']);
    }
}