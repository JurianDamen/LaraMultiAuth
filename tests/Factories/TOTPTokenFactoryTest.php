<?php

namespace RSpeekenbrink\LaraMultiAuth\Tests\Factories;

use Mockery as m;
use RSpeekenbrink\LaraMultiAuth\Factories\TOTPTokenFactory;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;
use PHPUnit\Framework\TestCase;

class TOTPTokenFactoryTest extends TestCase
{
    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function test_totp_token_can_be_created()
    {
        $factory = new TOTPTokenFactory();

        $result = $factory->make(1);

        $this->assertInstanceOf(TOTPToken::class, $result);
        $this->assertNotEmpty($result->token);
    }
}
