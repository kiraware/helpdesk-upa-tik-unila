<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;

class ConfigurationController extends Controller
{
    public function index()
    {
        $config = Configuration::firstOrNew([], [
            'upa_head_name' => '-',
            'upa_head_nip' => '-',
            'upa_head_position' => 'Kepala UPA TIK',
        ]);

        return view('configurations.index', compact('config'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'upa_head_name' => 'required|string|max:50',
            'upa_head_nip' => 'required|string|max:32',
            'upa_head_position' => 'required|string|max:50',
        ]);

        $config = Configuration::first();
        if (! $config) {
            $config = new Configuration;
        }

        $config->fill($validated);
        $config->save();

        return back()->with('success', 'Data Pejabat Penandatangan berhasil diperbarui.');
    }
}
