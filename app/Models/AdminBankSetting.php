<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminBankSetting extends Model
{
    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'qris_image_path',
        'instructions',
        'is_active',
        'midtrans_is_active',
        'midtrans_is_production',
        'midtrans_server_key',
        'midtrans_client_key',
        'midtrans_merchant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'midtrans_is_active' => 'boolean',
            'midtrans_is_production' => 'boolean',
        ];
    }

    public function getMidtransServerKey(): ?string
    {
        return $this->midtrans_server_key ?: config('services.midtrans.server_key');
    }

    public function getMidtransClientKey(): ?string
    {
        return $this->midtrans_client_key ?: config('services.midtrans.client_key');
    }

    public function getMidtransMerchantId(): ?string
    {
        return $this->midtrans_merchant_id ?: config('services.midtrans.merchant_id');
    }

    public function isMidtransProduction(): bool
    {
        return $this->midtrans_is_production ?? config('services.midtrans.is_production', false);
    }

    public function isMidtransActive(): bool
    {
        return $this->midtrans_is_active ?? true;
    }

    public static function current(): self
    {
        return static::query()->where('is_active', true)->first() ?? static::create([
            'bank_name' => 'Bank Central Asia (BCA)',
            'bank_code' => 'BCA',
            'account_number' => '8830192841',
            'account_name' => 'Yayasan SafeGive Kebaikan Indonesia',
            'instructions' => 'Mohon transfer sesuai nominal yang dipilih. Cantumkan kode referensi donasi pada berita transfer untuk mempercepat verifikasi otomatis/admin.',
            'is_active' => true,
            'midtrans_is_active' => true,
            'midtrans_is_production' => false,
            'midtrans_server_key' => config('services.midtrans.server_key'),
            'midtrans_client_key' => config('services.midtrans.client_key'),
        ]);
    }
}
