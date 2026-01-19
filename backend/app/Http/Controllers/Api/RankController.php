<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index()
    {
        $ranks = Rank::with('employees')->get();
        return response()->json([
            'success' => true,
            'data' => $ranks
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:rank',
        ]);

        $rank = Rank::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rank created successfully',
            'data' => $rank
        ], 201);
    }

    public function show($id)
    {
        $rank = Rank::with('employees')->find($id);

        if (!$rank) {
            return response()->json([
                'success' => false,
                'message' => 'Rank not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $rank
        ]);
    }

    public function update(Request $request, $id)
    {
        $rank = Rank::find($id);

        if (!$rank) {
            return response()->json([
                'success' => false,
                'message' => 'Rank not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:100|unique:rank,name,' . $id,
        ]);

        $rank->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'message' => 'Rank updated successfully',
            'data' => $rank
        ]);
    }

    public function destroy($id)
    {
        $rank = Rank::find($id);

        if (!$rank) {
            return response()->json([
                'success' => false,
                'message' => 'Rank not found'
            ], 404);
        }

        $rank->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rank deleted successfully'
        ]);
    }
}
