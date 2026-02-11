<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgUnitType;
use Illuminate\Http\Request;

class OrgUnitTypeController extends Controller
{
    /**
     * Display a listing of org unit types.
     */
    public function index()
    {
        $orgUnitTypes = OrgUnitType::withCount('orgUnits')->get();
        return view('admin.org-unit-types.index', compact('orgUnitTypes'));
    }

    /**
     * Store a newly created org unit type.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_type',
        ]);

        OrgUnitType::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.org-unit-types')
            ->with('success', 'Org Unit Type created successfully.');
    }

    /**
     * Update the specified org unit type.
     */
    public function update(Request $request, $id)
    {
        $orgUnitType = OrgUnitType::find($id);

        if (!$orgUnitType) {
            return redirect()
                ->route('admin.org-unit-types')
                ->with('error', 'Org Unit Type not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:orgunit_type,name,' . $id,
        ]);

        $orgUnitType->update([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.org-unit-types')
            ->with('success', 'Org Unit Type updated successfully.');
    }

    /**
     * Remove the specified org unit type.
     */
    public function destroy($id)
    {
        $orgUnitType = OrgUnitType::find($id);

        if (!$orgUnitType) {
            return redirect()
                ->route('admin.org-unit-types')
                ->with('error', 'Org Unit Type not found.');
        }

        if ($orgUnitType->orgUnits()->count() > 0) {
            return redirect()
                ->route('admin.org-unit-types')
                ->with('error', 'Cannot delete type with associated units.');
        }

        $orgUnitType->delete();

        return redirect()
            ->route('admin.org-unit-types')
            ->with('success', 'Org Unit Type deleted successfully.');
    }
}
