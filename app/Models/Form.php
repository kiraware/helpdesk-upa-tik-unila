<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    /** @use HasFactory<\Database\Factories\FormFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Agar routing menggunakan slug.
     * Contoh: Route::get('/form/{form}', ...) -> URL: /form/survei-kepuasan
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // --- RELASI (Akan error jika Model Question/Submission belum dibuat, abaikan dulu) ---

    /**
     * Satu Form punya banyak Pertanyaan
     */
    public function questions(): HasMany
    {
        return $this->hasMany(FormQuestion::class)->orderBy('order');
    }

    /**
     * Satu Form punya banyak Responden/Submisi
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
