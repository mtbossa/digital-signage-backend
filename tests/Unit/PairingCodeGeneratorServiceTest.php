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
        $this->assertIsString($code);
        $this->assertEquals(6, strlen($code));
    }
}
