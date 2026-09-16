<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'campaign_id',
        'donor_id',
        'is_anonymous',
        'donor_name',
        'encrypted_donor_name',
        'donor_email',
        'donor_phone',
        'payment_method',
        'reference_code',
        'snap_token',
        'snap_redirect_url',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_response',
        'payment_proof_path',
        'donor_note',
        'admin_notes',
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
            'is_anonymous' => 'boolean',
            'block_number' => 'integer',
            'confirmed_at' => 'datetime',
            'midtrans_response' => 'array',
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