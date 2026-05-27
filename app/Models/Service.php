<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $table = 'services';

    protected $fillable = [
        'name',
        'is_active',
        'show_to_guest',
        'show_to_user',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_to_guest' => 'boolean',
        'show_to_user' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
