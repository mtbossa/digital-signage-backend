<?php

namespace Tests\Unit;

use App\Services\PairingCodeGeneratorService;
use PHPUnit\Framework\TestCase;

class PairingCodeGeneratorServiceTest extends TestCase
{
    /** @test */
    public function should_return_a_6_length_string()
    {
        $service = new PairingCodeGeneratorService();
        $code = $service->generate();
        $this->assertIsString($code['code']);
        $this->assertEquals(6, strlen($code['code']));
    }

    /** @test */
    public function should_return_expires_at_with_datetime_five_minutes_from_now()
    {
        $expected_datetime = now()->addMinutes(5)->toIso8601String();
        $service = new PairingCodeGeneratorService();
        $result = $service->generate();
        $this->assertEquals($expected_datetime, $result['expires_at']->toIso8601String());
    }
}
