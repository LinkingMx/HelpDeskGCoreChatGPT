<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NewHire extends Model
{
    protected $fillable = [
        'employee_name',
        'employee_position',
        'start_date',
        'client_id',
        'direct_supervisor',
        'required_asset_types',
        'other_equipment',
        'additional_comments',
        'is_replacement',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'required_asset_types' => 'array',
        'is_replacement' => 'boolean',
    ];

    /**
     * Relación con el cliente (sucursal/área)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con el usuario que creó el registro
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación many-to-many con tipos de activos
     */
    public function assetTypes(): BelongsToMany
    {
        return $this->belongsToMany(AssetType::class, 'new_hire_asset_type');
    }

    /**
     * Accessor para obtener la lista de tipos de activos como string
     */
    public function getRequiredAssetTypesListAttribute(): string
    {
        if (! $this->required_asset_types) {
            return 'Ninguno';
        }

        $assetTypes = AssetType::whereIn('id', $this->required_asset_types)->pluck('name')->toArray();

        return implode(', ', $assetTypes);
    }

    /**
     * Accessor para obtener el badge del estado
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido'
        };
    }
}
