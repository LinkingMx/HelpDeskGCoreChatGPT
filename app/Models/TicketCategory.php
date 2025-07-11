<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icon',
        'time',
        'department_id',
        'is_active',
        'requires_approval',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'time' => 'integer',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    /**
     * Get the department that owns the category.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get categories that require approval.
     */
    public function scopeRequiresApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Check if this category is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if this category requires approval.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Get the SLA time formatted.
     */
    public function getFormattedSlaAttribute(): string
    {
        return $this->time.' hora'.($this->time !== 1 ? 's' : '');
    }
}
