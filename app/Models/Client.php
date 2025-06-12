<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'contact_phone',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model and set default values for nullable fields.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            // Set default empty values for nullable fields if they're not provided
            if (empty($client->contact_name)) {
                $client->contact_name = '';
            }
            if (empty($client->contact_email)) {
                $client->contact_email = '';
            }
            if (empty($client->contact_phone)) {
                $client->contact_phone = '';
            }
        });

        static::updating(function ($client) {
            // Handle updates as well
            if (is_null($client->contact_name)) {
                $client->contact_name = '';
            }
            if (is_null($client->contact_email)) {
                $client->contact_email = '';
            }
            if (is_null($client->contact_phone)) {
                $client->contact_phone = '';
            }
        });
    }

    /**
     * Get the users associated with this client.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the tickets associated with this client.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
