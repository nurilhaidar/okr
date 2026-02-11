<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OkrType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class OkrTypeController extends Controller
{
    /**
     * Display a listing of OKR types with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = OkrType::query();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchValue = $request->search;
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Filter by is_employee
        if ($request->has('is_employee') && $request->is_employee != '') {
            $query->where('is_employee', $request->is_employee);
        }

        // Order by
        $orderBy = $request->input('order_by', 'name');
        $orderDirection = $request->input('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $totalRecords = $query->count();
        $okrTypes = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $okrTypes,
            'pagination' => [
                'total' => $totalRecords,
                'per_page' => (int) $limit,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($totalRecords / $limit),
                'from' => ($page - 1) * $limit + 1,
                'to' => min($page * $limit, $totalRecords),
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_employee' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $okrType = OkrType::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'OKR type created successfully',
            'data' => $okrType,
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $okrType = OkrType::with('okrs')->find($id);

        if (!$okrType) {
            return response()->json([
                'success' => false,
                'message' => 'OKR type not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $okrType,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return response()->json([
                'success' => false,
                'message' => 'OKR type not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'is_employee' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $okrType->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'OKR type updated successfully',
            'data' => $okrType,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return response()->json([
                'success' => false,
                'message' => 'OKR type not found',
            ], 404);
        }

        $okrType->delete();

        return response()->json([
            'success' => true,
            'message' => 'OKR type deleted successfully',
        ]);
    }
}
