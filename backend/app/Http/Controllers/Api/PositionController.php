<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::with('employees')->get();
        return response()->json([
            'success' => true,
            'data' => $positions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:position',
        ]);

        $position = Position::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Position created successfully',
            'data' => $position
        ], 201);
    }

    public function show($id)
    {
        $position = Position::with('employees')->find($id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'Position not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $position
        ]);
    }

    public function update(Request $request, $id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'Position not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:position,name,' . $id,
        ]);

        $position->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'message' => 'Position updated successfully',
            'data' => $position
        ]);
    }

    public function destroy($id)
    {
        $position = Position::find($id);

        if (!$position) {
            return response()->json([
                'success' => false,
                'message' => 'Position not found'
            ], 404);
        }

        $position->delete();

        return response()->json([
            'success' => true,
            'message' => 'Position deleted successfully'
        ]);
    }
}
