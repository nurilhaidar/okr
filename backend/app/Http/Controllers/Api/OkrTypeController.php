<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OkrType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OkrTypeController extends Controller
{
    public function index()
    {
        $okrTypes = OkrType::all();

        return response()->json([
            'success' => true,
            'data' => $okrTypes,
        ]);
    }

    public function store(Request $request)
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

    public function show($id)
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

    public function update(Request $request, $id)
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

    public function destroy($id)
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
