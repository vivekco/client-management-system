<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DuplicateService;

class DuplicateServiceTest extends TestCase
{
    public function test_it_generates_same_signature_for_formatted_phone_numbers()
    {
        $service = new DuplicateService();

        $sig1 = $service->generateSignature(
            'ABC Pvt Ltd',
            'test@example.com',
            '9801234567'
        );

        $sig2 = $service->generateSignature(
            'ABC Pvt Ltd',
            'test@example.com',
            '980-123-4567'
        );

        $this->assertEquals($sig1, $sig2);
    }

    public function test_it_generates_different_signature_for_different_values()
    {
        $service = new DuplicateService();

        $sig1 = $service->generateSignature(
            'ABC Pvt Ltd',
            'test@example.com',
            '9801234567'
        );

        $sig2 = $service->generateSignature(
            'XYZ Pvt Ltd',
            'test@example.com',
            '9801234567'
        );

        $this->assertNotEquals($sig1, $sig2);
    }
}
