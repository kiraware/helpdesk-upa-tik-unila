<?php

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DivisionController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::query()
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'ilike', "%{$q}%");
            })
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('divisions.index', compact('divisions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['name' => 'required|string|max:50|unique:divisions']);

        Division::create(['name' => $validated['name']]);

        return redirect()->route('divisions.index')->with('success', 'Unit Fungsi berhasil ditambahkan.');
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('divisions')->ignore($division->id)],
        ]);

        $division->update(['name' => $validated['name']]);

        return redirect()->route('divisions.index')->with('success', 'Unit Fungsi berhasil diperbarui.');
    }

    public function destroy(Division $division)
    {
        $division->delete();

        return redirect()->route('divisions.index')->with('success', 'Unit Fungsi berhasil dihapus.');
    }
}
