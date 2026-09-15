<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'campaign_id',
        'donor_id',
        'encrypted_amount',
        'amount_commitment',
        'transaction_hash',
        'block_number',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'block_number' => 'integer',
            'confirmed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }
}