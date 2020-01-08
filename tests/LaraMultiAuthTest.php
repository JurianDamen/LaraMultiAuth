<?php

namespace RSpeekenbrink\LaraMultiAuth\Tests;

use Mockery;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use PHPUnit\Framework\TestCase;
use RSpeekenbrink\LaraMultiAuth\HasTOTPAuth;
use RSpeekenbrink\LaraMultiAuth\LaraMultiAuth;

class LaraMultiAuthTest extends TestCase
{
    public function test_class_can_be_instantiated()
    {
        $reflectionClass = new \ReflectionClass(LaraMultiAuth::class);

        $this->assertTrue($reflectionClass->isInstantiable());
        $this->assertEquals('RSpeekenbrink\LaraMultiAuth', $reflectionClass->getNamespaceName());
    }

    public function test_migrations_can_be_ignored()
    {
        LaraMultiAuth::ignoreMigrations();
        $this->assertFalse(LaraMultiAuth::$runsMigrations);

        LaraMultiAuth::$runsMigrations = true;
        $this->assertTrue(LaraMultiAuth::$runsMigrations);
    }

    public function test_multiauth_can_be_disabled()
    {
        LaraMultiAuth::disable();
        $this->assertFalse(LaraMultiAuth::$enabled);

        LaraMultiAuth::$enabled = true;
        $this->assertTrue(LaraMultiAuth::$enabled);
    }

    public function test_can_handle_request_succesfully()
    {
        Auth::shouldReceive('logout');
        Session::shouldReceive('put')->with('laramultiauth.id', 1)->once();
        Redirect::shouldReceive('route')->with(LaraMultiAuth::$totpRoute)->once();

        LaraMultiAuth::handle(new Request(), new User());
    }

    /**
     * @expectedException TypeError
     */
    public function test_exception_gets_thrown_when_invalid_user_got_passed_to_logoutAndRedirectToTokenScreen()
    {
        LaraMultiAuth::logoutAndRedirectToTokenScreen(null);
    }

    public function test_handle_returns_false_when_multiauth_is_disabled()
    {
        LaraMultiAuth::disable();

        $this->assertFalse(LaraMultiAuth::handle(new Request(), new User()));
    }

    public function test_check_if_multiauth_is_enabled_returns_false_on_user_without_token()
    {
        $user = new User();
        $user->totpToken = null;

        $this->assertFalse(LaraMultiAuth::isMultiAuthEnabledForUser($user));
    }

    public function test_redirect_path_can_be_set()
    {
        $path = '/random/test/path';

        LaraMultiAuth::setRedirectPath($path);
        $this->assertEquals($path, LaraMultiAuth::$redirectPath);
    }

    public function test_routes_function_registers_package_routes()
    {
        Route::shouldReceive('namespace')
            ->with('\RSpeekenbrink\LaraMultiAuth\Http\Controllers')
            ->once()
            ->andReturnSelf();
        Route::shouldReceive('group')->once();


        LaraMultiAuth::routes();
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
