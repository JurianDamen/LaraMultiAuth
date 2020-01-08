<?php

namespace RSpeekenbrink\LaraMultiAuth\Tests\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use RSpeekenbrink\LaraMultiAuth\HasTOTPAuth;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;
use Illuminate\Container\Container;
use RSpeekenbrink\LaraMultiAuth\Services\TOTPService;

class TOTPTokenTest extends TestCase
{
    public function tearDown()
    {
        m::close();
        Container::getInstance()->flush();
        parent::tearDown();
    }

    public function test_model_can_be_instantiated()
    {
        $reflectionClass = new \ReflectionClass(TOTPToken::class);

        $this->assertTrue($reflectionClass->isInstantiable());
        $this->assertTrue($reflectionClass->hasProperty('table'));
        $this->assertEquals('RSpeekenbrink\LaraMultiAuth\Models', $reflectionClass->getNamespaceName());
    }

    public function test_token_can_be_generated()
    {
        $testKey = 'TESTKEY';
        $container = new Container();
        Container::setInstance($container);

        $container->instance(TOTPService::class, $service = m::mock());
        $service->shouldReceive('generateSecret')->andReturn($testKey);

        $token = new TOTPToken();
        $token->generateToken();

        $this->assertEquals($token->token, $testKey);
    }

    public function test_has_user_relationship()
    {
        $token = m::mock(TOTPToken::class)->makePartial();

        $token->shouldReceive('belongsTo')
            ->with('auth.providers.auth.guards.api.provider.model', 'user_id')
            ->once()
            ->andReturn(new User());

        $this->assertEquals($token->user(), new User());
    }
}

class User implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable, HasTOTPAuth;

    public $totpToken = 'TESTTOKEN';

    public function getKey()
    {
        return 1;
    }
}
