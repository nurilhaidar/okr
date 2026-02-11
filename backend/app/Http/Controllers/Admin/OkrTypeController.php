<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OkrType;
use Illuminate\Http\Request;

class OkrTypeController extends Controller
{
    /**
     * Display a listing of OKR types.
     */
    public function index()
    {
        $okrTypes = OkrType::withCount('okrs')->get();
        return view('admin.okr-types.index', compact('okrTypes'));
    }

    /**
     * Store a newly created OKR type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:okr_type',
            'is_employee' => 'boolean',
        ]);

        OkrType::create([
            'name' => $request->name,
            'is_employee' => $request->has('is_employee'),
        ]);

        return redirect()
            ->route('admin.okr-types')
            ->with('success', 'OKR Type created successfully.');
    }

    /**
     * Update the specified OKR type.
     */
    public function update(Request $request, $id)
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return redirect()
                ->route('admin.okr-types')
                ->with('error', 'OKR Type not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:okr_type,name,' . $id,
            'is_employee' => 'boolean',
        ]);

        $okrType->update([
            'name' => $request->name,
            'is_employee' => $request->has('is_employee'),
        ]);

        return redirect()
            ->route('admin.okr-types')
            ->with('success', 'OKR Type updated successfully.');
    }

    /**
     * Remove the specified OKR type.
     */
    public function destroy($id)
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return redirect()
                ->route('admin.okr-types')
                ->with('error', 'OKR Type not found.');
        }

        if ($okrType->okrs()->count() > 0) {
            return redirect()
                ->route('admin.okr-types')
                ->with('error', 'Cannot delete OKR type with associated OKRs.');
        }

        $okrType->delete();

        return redirect()
            ->route('admin.okr-types')
            ->with('success', 'OKR Type deleted successfully.');
    }

    /**
     * Get all OKR types for dropdown.
     */
    public function getAll()
    {
        $okrTypes = OkrType::all()->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'is_employee' => $type->is_employee,
            ];
        });

        return response()->json($okrTypes);
    }
}
