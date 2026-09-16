<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use phpseclib3\Math\BigInteger;

class PaillierService
{
    private const KEY_PATH = 'paillier/keys.json';
    private const KEY_BITS = 1024;

    /** @return array{n: string, g: string} */
    public function publicKey(): array
    {
        $keys = $this->keys();

        return ['n' => $keys['n'], 'g' => $keys['g']];
    }

    public function encrypt(int $amount): string
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Paillier plaintext must be non-negative.');
        }

        $keys = $this->keys();
        $n = $this->integer($keys['n']);
        $g = $this->integer($keys['g']);
        $nSquared = $n->multiply($n);
        $message = new BigInteger($amount);
        $r = $this->randomCoprime($n);
        $ciphertext = $g->powMod($message, $nSquared)
            ->multiply($r->powMod($n, $nSquared))
            ->divide($nSquared)[1];

        return $ciphertext->toString();
    }

    public function encryptString(string $text): string
    {
        $keys = $this->keys();
        $n = $this->integer($keys['n']);
        $g = $this->integer($keys['g']);
        $nSquared = $n->multiply($n);

        $hex = bin2hex($text);
        if ($hex === '') {
            $hex = '00';
        }

        $message = new BigInteger($hex, 16);
        if ($message->compare($n) >= 0) {
            throw new InvalidArgumentException('Plaintext string exceeds Paillier modulus size.');
        }

        $r = $this->randomCoprime($n);
        $ciphertext = $g->powMod($message, $nSquared)
            ->multiply($r->powMod($n, $nSquared))
            ->divide($nSquared)[1];

        return $ciphertext->toString();
    }

    public function decryptString(string $ciphertext): string
    {
        $keys = $this->keys();
        $n = $this->integer($keys['n']);
        $nSquared = $n->multiply($n);
        $lambda = $this->integer($keys['lambda']);
        $mu = $this->integer($keys['mu']);
        $lValue = $this->lFunction($this->integer($ciphertext)->powMod($lambda, $nSquared), $n);
        $message = $lValue->multiply($mu)->divide($n)[1];

        $hex = $message->toHex();
        if ($hex === '00' || $hex === '0') {
            return '';
        }
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }

        $decoded = hex2bin($hex);

        return $decoded !== false ? $decoded : '';
    }

    public function commitment(string $ciphertext): string
    {
        return hash('sha256', $ciphertext);
    }

    public function add(string ...$ciphertexts): string
    {
        if ($ciphertexts === []) {
            return '1';
        }

        $n = $this->integer($this->keys()['n']);
        $nSquared = $n->multiply($n);
        $sum = new BigInteger(1);

        foreach ($ciphertexts as $ciphertext) {
            $sum = $sum->multiply($this->integer($ciphertext))->divide($nSquared)[1];
        }

        return $sum->toString();
    }

    public function decrypt(string $ciphertext): int
    {
        $keys = $this->keys();
        $n = $this->integer($keys['n']);
        $nSquared = $n->multiply($n);
        $lambda = $this->integer($keys['lambda']);
        $mu = $this->integer($keys['mu']);
        $lValue = $this->lFunction($this->integer($ciphertext)->powMod($lambda, $nSquared), $n);
        $message = $lValue->multiply($mu)->divide($n)[1];

        $maxInt = new BigInteger(PHP_INT_MAX);
        if ($message->compare($maxInt) > 0 || $message->compare(new BigInteger(0)) < 0) {
            return 0;
        }

        return (int) $message->toString();
    }

    /** @return array<string, string> */
    private function keys(): array
    {
        $disk = Storage::disk('private');

        if (! $disk->exists(self::KEY_PATH)) {
            $this->generateKeys();
        }

        $keys = json_decode($disk->get(self::KEY_PATH), true);

        if (! is_array($keys) || ! isset($keys['n'], $keys['g'], $keys['lambda'], $keys['mu'])) {
            throw new InvalidArgumentException('Paillier key file is invalid.');
        }

        return $keys;
    }

    public function generateKeys(): void
    {
        $halfBits = intdiv(self::KEY_BITS, 2);
        $p = BigInteger::randomPrime($halfBits);
        $q = BigInteger::randomPrime($halfBits);
        $n = $p->multiply($q);
        $lambda = $this->lcm($p->subtract(new BigInteger(1)), $q->subtract(new BigInteger(1)));
        $mu = $this->lFunction($n->add(new BigInteger(1))->powMod($lambda, $n->multiply($n)), $n)->modInverse($n);

        if ($mu === false) {
            throw new InvalidArgumentException('Unable to create Paillier inverse.');
        }

        Storage::disk('private')->put(self::KEY_PATH, json_encode([
            'n' => $n->toString(),
            'g' => $n->add(new BigInteger(1))->toString(),
            'lambda' => $lambda->toString(),
            'mu' => $mu->toString(),
        ], JSON_THROW_ON_ERROR));
    }

    private function randomCoprime(BigInteger $n): BigInteger
    {
        do {
            $r = BigInteger::randomRange(new BigInteger(1), $n->subtract(new BigInteger(1)));
        } while (! $r->gcd($n)->equals(new BigInteger(1)));

        return $r;
    }

    private function lcm(BigInteger $left, BigInteger $right): BigInteger
    {
        return $left->multiply($right)->divide($left->gcd($right))[0];
    }

    private function lFunction(BigInteger $value, BigInteger $n): BigInteger
    {
        return $value->subtract(new BigInteger(1))->divide($n)[0];
    }

    private function integer(string $value): BigInteger
    {
        return new BigInteger($value, 10);
    }
}
