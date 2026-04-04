<?php

namespace App\Support\Base62;

use Base62\Base62 as VendorBase62;
use InvalidArgumentException;

class NativeBase62 extends VendorBase62
{
    public function __construct()
    {
        parent::__construct();
    }

    public function encode($number): string
    {
        if (! $this->isValidNumber($number)) {
            throw new InvalidArgumentException('Invalid number for BasicEncoder');
        }

        if ($number === 0 || $number === '0') {
            return '0';
        }

        $number = (int) $number;
        $encoded = '';

        while ($number > 0) {
            $encoded = self::CHARS[$number % self::BASE_LENGTH] . $encoded;
            $number = intdiv($number, self::BASE_LENGTH);
        }

        return $encoded;
    }

    public function decode($base62)
    {
        if (! is_string($base62) || ! preg_match('/^[a-zA-Z0-9]+$/', $base62)) {
            throw new InvalidArgumentException('Must be a base 62 valid string');
        }

        $value = '0';

        foreach (str_split($base62) as $character) {
            $digit = strpos(self::CHARS, $character);
            $value = $this->addDecimalStrings(
                $this->multiplyDecimalStringByInt($value, self::BASE_LENGTH),
                (string) $digit
            );
        }

        return $value;
    }

    private function isValidNumber(mixed $number): bool
    {
        return is_int($number)
            ? $number >= 0
            : (is_string($number) && ctype_digit($number));
    }

    private function multiplyDecimalStringByInt(string $number, int $multiplier): string
    {
        if ($number === '0' || $multiplier === 0) {
            return '0';
        }

        $carry = 0;
        $result = [];

        foreach (array_reverse(str_split($number)) as $digit) {
            $product = ((int) $digit * $multiplier) + $carry;
            $result[] = (string) ($product % 10);
            $carry = intdiv($product, 10);
        }

        while ($carry > 0) {
            $result[] = (string) ($carry % 10);
            $carry = intdiv($carry, 10);
        }

        return ltrim(strrev(implode('', $result)), '0') ?: '0';
    }

    private function addDecimalStrings(string $left, string $right): string
    {
        $leftDigits = array_reverse(str_split($left));
        $rightDigits = array_reverse(str_split($right));
        $length = max(count($leftDigits), count($rightDigits));
        $carry = 0;
        $result = [];

        for ($index = 0; $index < $length; $index++) {
            $sum = ((int) ($leftDigits[$index] ?? 0)) + ((int) ($rightDigits[$index] ?? 0)) + $carry;
            $result[] = (string) ($sum % 10);
            $carry = intdiv($sum, 10);
        }

        while ($carry > 0) {
            $result[] = (string) ($carry % 10);
            $carry = intdiv($carry, 10);
        }

        return ltrim(strrev(implode('', $result)), '0') ?: '0';
    }
}

