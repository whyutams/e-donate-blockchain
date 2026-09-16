<?php

namespace App\Services;

class DonationAmountEncryptor
{
    public function __construct(private readonly PaillierService $paillier)
    {
    }

    public function encrypt(int $amount): string
    {
        return $this->paillier->encrypt($amount);
    }
}