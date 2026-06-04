<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::query()
            ->when($request->q, function ($query, $q) {
                $query->where('name', 'ilike', "%{$q}%");
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->when($request->guest !== null && $request->guest !== '', function ($query) use ($request) {
                $query->where('show_to_guest', $request->guest);
            })
            ->when($request->user !== null && $request->user !== '', function ($query) use ($request) {
                $query->where('show_to_user', $request->user);
            })
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->paginate(10)
            ->withQueryString();

        return view('services.index', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:services',
            'is_active' => 'required|boolean',
            'show_to_guest' => 'required|boolean',
            'show_to_user' => 'required|boolean',
        ]);

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('services')->ignore($service->id)],
            'is_active' => 'required|boolean',
            'show_to_guest' => 'required|boolean',
            'show_to_user' => 'required|boolean',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Layanan berhasil dihapus.');
    }
}
