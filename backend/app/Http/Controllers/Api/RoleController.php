<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Role::with('employees');

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $searchValue = $request->search;
            $query->where('name', 'like', '%' . $searchValue . '%');
        }

        // Order by
        $orderBy = $request->input('order_by', 'name');
        $orderDirection = $request->input('order_direction', 'asc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $totalRecords = $query->count();
        $roles = $query->offset(($page - 1) * $limit)->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $roles,
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
        $request->validate([
            'name' => 'required|string|max:100|unique:role',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $role = Role::with('employees')->find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:role,name,' . $id,
        ]);

        $role->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found'
            ], 404);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }
}
