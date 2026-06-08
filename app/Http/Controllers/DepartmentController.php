<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::query()
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'ilike', "%{$q}%");
            })
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:50|unique:departments']);

        Department::create(['name' => $validated['name']]);

        return redirect()->route('departments.index')->with('success', 'Unit Kerja berhasil ditambahkan.');
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('departments')->ignore($department->id)],
        ]);

        $department->update(['name' => $validated['name']]);

        return redirect()->route('departments.index')->with('success', 'Unit Kerja berhasil diperbarui.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Unit Kerja berhasil dihapus.');
    }
}
