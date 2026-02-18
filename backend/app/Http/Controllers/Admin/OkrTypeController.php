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
        $employees = \App\Models\Employee::active()->get(['id', 'name']);
        $orgUnits = \App\Models\OrgUnit::active()->get(['id', 'name']);
        return view('admin.okr-types.index', compact('okrTypes', 'employees', 'orgUnits'));
    }

    /**
     * Show the form for creating a new OKR type.
     */
    public function create()
    {
        return view('admin.okr-types.create');
    }

    /**
     * Store a newly created OKR type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:okr_type',
            'is_active' => 'boolean',
        ]);

        OkrType::create([
            'name' => $request->name,
            'is_employee' => $request->boolean('is_employee'),
            'is_active' => $request->boolean('is_active', true),
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
            'is_active' => 'boolean',
        ]);

        $okrType->update([
            'name' => $request->name,
            'is_employee' => $request->boolean('is_employee'),
            'is_active' => $request->boolean('is_active', true),
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
     * Deactivate the specified OKR type.
     */
    public function deactivate(Request $request, $id)
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return redirect()
                ->route('admin.okr-types')
                ->with('error', 'OKR Type not found.');
        }

        $okrType->update(['is_active' => false]);

        return redirect()
            ->route('admin.okr-types')
            ->with('success', 'OKR Type deactivated successfully.');
    }

    /**
     * Activate the specified OKR type.
     */
    public function activate(Request $request, $id)
    {
        $okrType = OkrType::find($id);

        if (!$okrType) {
            return redirect()
                ->route('admin.okr-types')
                ->with('error', 'OKR Type not found.');
        }

        $okrType->update(['is_active' => true]);

        return redirect()
            ->route('admin.okr-types')
            ->with('success', 'OKR Type activated successfully.');
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
                'is_active' => $type->is_active ?? true,
            ];
        });

        return response()->json($okrTypes);
    }
}
