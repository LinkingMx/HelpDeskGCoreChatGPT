<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'is_active',
        'is_final',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_final' => 'boolean',
    ];

    /**
     * Get the badge color attribute.
     */
    public function getBadgeColorAttribute(): string
    {
        return $this->color;
    }

    /**
     * Scope to get only active statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only final statuses.
     */
    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    /**
     * Scope to get only non-final statuses.
     */
    public function scopeNonFinal($query)
    {
        return $query->where('is_final', false);
    }

    /**
     * Check if this is a final status.
     */
    public function isFinal(): bool
    {
        return $this->is_final;
    }

    /**
     * Check if this status is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}
