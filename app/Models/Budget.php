<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Budget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'currency',
        'period_start',
        'period_end',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('user_filter', function ($query) {
            if (Auth::check()) {
                $query->where('user_id', Auth::id());
            }
        });

        static::creating(function ($budget) {
            if (Auth::check()) {
                $budget->user_id = Auth::id();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
