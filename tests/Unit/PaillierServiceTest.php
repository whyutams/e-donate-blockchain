<?php

namespace Tests\Unit;

use App\Services\PaillierService;
use Tests\TestCase;

class PaillierServiceTest extends TestCase
{
    public function test_it_encrypts_and_decrypts_integers_correctly(): void
    {
        $service = app(PaillierService::class);
        $amount = 500000;
        $ciphertext = $service->encrypt($amount);

        $this->assertIsString($ciphertext);
        $this->assertNotEmpty($ciphertext);
        $this->assertSame($amount, $service->decrypt($ciphertext));
    }

    public function test_it_encrypts_and_decrypts_strings_correctly(): void
    {
        $service = app(PaillierService::class);
        
        $names = [
            'Budi Santoso',
            'Siti Nurhaliza 123',
            'Donatur Rahasia Berkah',
            'Dr. Ir. Raden Mas Arya, S.Kom., M.T.',
        ];

        foreach ($names as $name) {
            $ciphertext = $service->encryptString($name);
            $this->assertIsString($ciphertext);
            $this->assertNotEmpty($ciphertext);
            $this->assertNotEquals($name, $ciphertext);

            $decrypted = $service->decryptString($ciphertext);
            $this->assertSame($name, $decrypted);
        }
    }

    public function test_it_generates_valid_sha256_commitment(): void
    {
        $service = app(PaillierService::class);
        $ciphertext = $service->encryptString('Ahmad Fauzi');
        $commitment = $service->commitment($ciphertext);

        $this->assertSame(64, strlen($commitment));
        $this->assertSame(hash('sha256', $ciphertext), $commitment);
    }
}
