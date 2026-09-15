<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Campaign extends Model
{
    protected $fillable = [
        'organizer_id',
        'title',
        'slug',
        'description',
        'category',
        'image_path',
        'target_amount',
        'encrypted_collected_amount',
        'progress_percentage',
        'donors_count',
        'starts_at',
        'ends_at',
        'status',
        'wallet_address',
        'blockchain_campaign_id',
        'withdrawal_transaction_hash',
        'withdrawal_status',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'integer',
            'progress_percentage' => 'decimal:2',
            'donors_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function acceptsDonations(?Carbon $at = null): bool
    {
        $at ??= now();

        return $this->status === 'active'
            && $this->starts_at !== null
            && $this->ends_at !== null
            && $at->betweenIncluded($this->starts_at, $this->ends_at);
    }

    public function donationAvailabilityMessage(?Carbon $at = null): string
    {
        $at ??= now();

        if ($this->status !== 'active') {
            return 'Kampanye tidak berstatus aktif.';
        }

        if ($this->starts_at && $at->isBefore($this->starts_at)) {
            return 'Donasi dibuka pada '.$this->starts_at->translatedFormat('d F Y, H:i').' WIB.';
        }

        if ($this->ends_at && $at->isAfter($this->ends_at)) {
            return 'Batas waktu donasi sudah lewat pada '.$this->ends_at->translatedFormat('d F Y, H:i').' WIB.';
        }

        return 'Jadwal donasi belum lengkap.';
    }

    public function canWithdraw(?Carbon $at = null): bool
    {
        $at ??= now();

        return in_array($this->status, ['active', 'goal_reached', 'expired'], true)
            && $this->withdrawal_status === 'not_ready'
            && ((float) $this->progress_percentage >= 100 || ($this->ends_at && $at->isAfter($this->ends_at)));
    }
}