<?php

namespace App\Http\Controllers;

use App\Helpers\ImageSanitizer;
use App\Models\Faq;
use App\Rules\SafeFile;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function show()
    {
        $faq = Faq::first();

        return view('faq', compact('faq'));
    }

    public function edit()
    {
        $faq = Faq::first() ?? new Faq;

        return view('faqs.edit', compact('faq'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
        ]);

        $faq = Faq::first() ?? new Faq;
        $faq->description = $validated['description'] ?? '';
        $faq->save();

        return back()->with('success', 'FAQ berhasil diperbarui.');
    }

    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf', new SafeFile],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('faq-attachments', 'public');

            ImageSanitizer::sanitize(storage_path('app/public/'.$path), $file->getClientOriginalExtension());

            return response()->json([
                'url' => asset('storage/'.$path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
