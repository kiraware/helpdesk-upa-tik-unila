<?php

namespace App\Http\Controllers;

use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = SurveyQuestion::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('aspect_name', 'ilike', "%{$q}%")
                        ->orWhere('satisfaction_question', 'ilike', "%{$q}%")
                        ->orWhere('importance_question', 'ilike', "%{$q}%");
                });
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->withCount('answers')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('survey-questions.index', compact('questions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'aspect_name' => 'required|string|max:100',
            'satisfaction_question' => 'required|string|max:500',
            'importance_question' => 'required|string|max:500',
            'sort_order' => 'required|integer|min:0|max:999',
            'is_active' => 'required|boolean',
        ]);

        SurveyQuestion::create($validated);

        return redirect()->route('survey-questions.index')->with('success', 'Pertanyaan kuesioner berhasil ditambahkan.');
    }

    public function update(Request $request, SurveyQuestion $surveyQuestion)
    {
        $validated = $request->validate([
            'aspect_name' => 'required|string|max:100',
            'satisfaction_question' => 'required|string|max:500',
            'importance_question' => 'required|string|max:500',
            'sort_order' => 'required|integer|min:0|max:999',
            'is_active' => 'required|boolean',
        ]);

        $surveyQuestion->update($validated);

        return redirect()->route('survey-questions.index')->with('success', 'Pertanyaan kuesioner berhasil diperbarui.');
    }

    public function destroy(SurveyQuestion $surveyQuestion)
    {
        if ($surveyQuestion->answers()->exists()) {
            return redirect()->route('survey-questions.index')
                ->with('error', 'Pertanyaan tidak dapat dihapus karena sudah memiliki jawaban survei.');
        }

        $surveyQuestion->delete();

        return redirect()->route('survey-questions.index')->with('success', 'Pertanyaan kuesioner berhasil dihapus.');
    }

    public function toggleActive(SurveyQuestion $surveyQuestion)
    {
        $surveyQuestion->update(['is_active' => ! $surveyQuestion->is_active]);

        $status = $surveyQuestion->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('survey-questions.index')->with('success', "Pertanyaan kuesioner berhasil {$status}.");
    }
}
