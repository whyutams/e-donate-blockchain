<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationProfile extends Model
{
    protected $fillable = [
        'user_id',
        'entity_type',
        'legal_name',
        'individual_nik',
        'individual_address',
        'individual_call_center',
        'individual_selfie_path',
        'individual_ktp_path',
        'foundation_responsible_name',
        'foundation_npwp',
        'foundation_call_center',
        'foundation_legal_document_path',
        'identity_document_path',
        'encrypted_identity_data',
        'status',
        'review_notes',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'individual_nik' => 'encrypted',
            'individual_address' => 'encrypted',
            'foundation_npwp' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}