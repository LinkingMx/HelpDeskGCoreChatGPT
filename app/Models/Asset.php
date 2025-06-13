<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'asset_tag',
        'serial_number',
        'asset_type_id',
        'asset_status_id',
        'assigned_to',
        'assigned_user_id',
        'client_id',
        'brand_id',
        'supplier',
        'invoice_number',
        'model',
        'purchase_date',
        'purchase_cost',
        'warranty_expires_on',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expires_on' => 'date',
        'purchase_cost' => 'decimal:2',
    ];

    /**
     * Get the asset type that owns the asset.
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    /**
     * Get the asset status that owns the asset.
     */
    public function assetStatus(): BelongsTo
    {
        return $this->belongsTo(AssetStatus::class);
    }

    /**
     * Get the client that owns the asset.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the brand that owns the asset.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the user assigned to this asset.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get the attachments for this asset.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssetAttachment::class);
    }
}
