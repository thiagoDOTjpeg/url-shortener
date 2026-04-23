<?php

/* @noinspection PhpIllegalPsrClassPathInspection */
namespace Tests\Unit\Support;

use App\Support\Base62\NativeBase62;
use Tests\TestCase;

class NativeBase62Test extends TestCase {

    private readonly NativeBase62 $base62;

    public function setUp(): void {
        parent::setUp();
        $this->base62 = new NativeBase62();
    }

    public function test_should_successfully_encode_number() {
        $number = 123;
        $expected = '1z';

        $base62 = $this->base62;

        $result = $base62->encode($number);

        $this->assertEquals($expected, $result);
    }

    public function test_should_throws_an_error_if_the_number_is_invalid_when_trying_to_encode_number() {
        $invalidNumber = 'error';

        $base62 = $this->base62;

        $this->assertThrows(function() use ($base62, $invalidNumber) {
            $base62->encode($invalidNumber);
        }, \InvalidArgumentException::class);
    }

    public function test_should_returns_0_if_given_number_is_0_on_encode() {
        $base62 = $this->base62;

        $result = $base62->encode(0);

        $this->assertEquals("0", $result);
    }

    public function test_should_decode_number_successfully() {
        $expected = '1z';

        $base62 = $this->base62;
        $result = $base62->decode($expected);

        $this->assertEquals(123, $result);
    }

    public function test_should_throws_an_error_if_the_number_is_invalid_when_trying_to_decode_number()
    {
        $invalidNumber = 123;

        $base62 = $this->base62;

        $this->assertThrows(function () use ($base62, $invalidNumber) {
            $base62->decode($invalidNumber);
        }, \InvalidArgumentException::class);
    }

    public function test_should_throws_an_error_if_receives_a_empty_string_to_decode() {
        $base62 = $this->base62;

        $this->assertThrows(function () use ($base62) {
            $base62->decode('');
        }, \InvalidArgumentException::class);
    }

    public function test_should_successfully_encode_php_int_max() {
        $maxInt = PHP_INT_MAX;

        $base62 = $this->base62;
        $result = $base62->encode($maxInt);

        $this->assertNotEquals($maxInt, $result);
    }

    public function test_should_successfully_encode_php_int_max_plus_one() {
        $maxInt = PHP_INT_MAX + 1;

        $base62 = $this->base62;

        $this->assertThrows(function () use ($base62, $maxInt) {
            $base62->encode($maxInt);
        }, \InvalidArgumentException::class);
    }

    public function test_round_trip_with_big_numbers() {
        $bigNumber = '123456789012345678901234567890';

        $encoded = $this->base62->encode($bigNumber);
        $decoded = $this->base62->decode($encoded);

        $this->assertEquals($bigNumber, $decoded);
    }
}


