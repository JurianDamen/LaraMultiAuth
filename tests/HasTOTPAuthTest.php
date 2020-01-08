<?php

namespace RSpeekenbrink\LaraMultiAuth\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use RSpeekenbrink\LaraMultiAuth\Factories\TOTPTokenFactory;
use RSpeekenbrink\LaraMultiAuth\HasTOTPAuth;
use Illuminate\Container\Container;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;

class HasTOTPAuthTest extends TestCase
{
    public function tearDown()
    {
        m::close();
        Container::getInstance()->flush();
        parent::tearDown();
    }

    public function test_totp_token_can_be_created()
    {
        $container = new Container();
        Container::setInstance($container);

        $container->instance(TOTPTokenFactory::class, $factory = m::mock());
        $factory->shouldReceive('make')->once()->with(1);

        $user = new HasTOTPAuthTestStub();

        $user->createTotpToken();
    }

    public function test_totptoken_relation_function_calls_hasone()
    {
        $user = new HasTOTPAuthTestStub();

        $result = $user->totpToken();

        $this->assertEquals([TOTPToken::class, 'user_id'], $result);
    }
}

class HasTOTPAuthTestStub
{
    use HasTOTPAuth;

    public function getKey()
    {
        return 1;
    }

    public function hasOne($class, $id)
    {
        return [$class, $id];
    }
}
