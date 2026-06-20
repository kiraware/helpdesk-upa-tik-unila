<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceReplyTemplate;
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
            ->withCount('tickets')
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->paginate(10)
            ->withQueryString();

        // Eager load template jawaban milik admin yang sedang login
        $myTemplates = ServiceReplyTemplate::where('user_id', auth()->id())
            ->pluck('template', 'service_id');

        return view('services.index', compact('services', 'myTemplates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:services',
            'is_active' => 'required|boolean',
            'show_to_guest' => 'required|boolean',
            'show_to_user' => 'required|boolean',
            'notes' => 'nullable|string',
            'reply_template' => 'nullable|string',
        ]);

        $service = Service::create($validated);

        // Simpan template jawaban jika diisi
        if (! empty($validated['reply_template'])) {
            ServiceReplyTemplate::create([
                'service_id' => $service->id,
                'user_id' => auth()->id(),
                'template' => $validated['reply_template'],
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('services')->ignore($service->id)],
            'is_active' => 'required|boolean',
            'show_to_guest' => 'required|boolean',
            'show_to_user' => 'required|boolean',
            'notes' => 'nullable|string',
            'reply_template' => 'nullable|string',
        ]);

        $service->update($validated);

        // Simpan atau update template jawaban
        if (! empty($validated['reply_template'])) {
            ServiceReplyTemplate::updateOrCreate(
                [
                    'service_id' => $service->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'template' => $validated['reply_template'],
                ]
            );
        } else {
            // Hapus template jika dikosongkan
            ServiceReplyTemplate::where('service_id', $service->id)
                ->where('user_id', auth()->id())
                ->delete();
        }

        return redirect()->route('services.index')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->tickets()->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'Layanan tidak dapat dihapus karena sudah digunakan pada tiket.');
        }

        $service->delete();

        return redirect()->route('services.index')->with('success', 'Layanan berhasil dihapus.');
    }

    public function toggleActive(Service $service)
    {
        $service->update(['is_active' => ! $service->is_active]);

        $status = $service->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('services.index')->with('success', "Layanan berhasil {$status}.");
    }
}
