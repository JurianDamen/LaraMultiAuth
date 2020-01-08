<?php

namespace RSpeekenbrink\LaraMultiAuth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class LaraMultiAuth
{
    /**
     * Indicates if LaraMultiAuth is enabled.
     *
     * @var bool
     */
    public static $enabled = true;

    /**
     * Defines the route used for TOTP Authentication.
     *
     * @var string
     */
    public static $totpRoute = 'laramultiauth.multiauth.show';

    /**
     * Path to redirect users to after authentication.
     *
     * @var string
     */
    public static $redirectPath = '/';

    /**
     * Indicates if LaraMultiAuth migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * Configure LaraMultiAuth to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Configure LaraMultiAuth to be disabled and not handle MultiAuth.
     *
     * @return static
     */
    public static function disable()
    {
        static::$enabled = false;

        return new static;
    }

    /**
     * Handle a Request and check if MultiAuth is enabled and should
     * be dealt with first.
     *
     * @param Request $request
     * @param Authenticatable $user
     * @return mixed
     */
    public static function handle(Request $request, Authenticatable $user)
    {
        if (static::$enabled && $user) {
            if (static::isMultiAuthEnabledForUser($user)) {
                return static::logoutAndRedirectToTokenScreen($user);
            }
        }

        return false;
    }

    /**
     * Log the current user out, store user id in session and redirect
     * to TOTP route.
     *
     * @param Authenticatable $user
     * @return mixed
     */
    public static function logoutAndRedirectToTokenScreen(Authenticatable $user)
    {
        Auth::logout();

        Session::put('laramultiauth.id', $user->getKey());

        return Redirect::route(static::$totpRoute);
    }

    /**
     * Returns if Multi Auth is enabled for the given user.
     *
     * @param Authenticatable $user
     * @return bool
     */
    public static function isMultiAuthEnabledForUser(Authenticatable $user)
    {
        if ($traits = class_uses($user)) {
            if (in_array(HasTOTPAuth::class, $traits)) {
                if (!empty($user->totpToken)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Set the path authenticated users will be redirected to.
     *
     * @param string $path
     * @return static
     */
    public static function setRedirectPath(string $path)
    {
        static::$redirectPath = $path;

        return new static;
    }

    /**
     * Returns if a supported QR Code Generation Library is available.
     *
     * @return bool
     */
    public static function checkIfQRGenerationIsAvailable()
    {
        return class_exists('BaconQrCode\Writer');
    }

    /**
     * Set the TOTP Token Verification Route to use.
     *
     * @param string $route
     * @return static
     */
    public static function setTOTPRoute(string $route)
    {
        static::$totpRoute = $route;

        return new static;
    }

    /**
     * Registers Routes that LaraMultiAuth uses.
     *
     * @return void
     */
    public static function routes()
    {
        Route::namespace('\RSpeekenbrink\LaraMultiAuth\Http\Controllers')->group(function () {
            Route::get('/login/multiauth', 'TOTPController@showTokenScreen')->name('laramultiauth.multiauth.show');
            Route::post('/login/multiauth', 'TOTPController@verifyToken')->name('laramultiauth.multiauth.post');
            Route::get('/multiauth/setup', 'TOTPController@showSetup')->name('laramultiauth.setup.show');
            Route::post('/multiauth/setup', 'TOTPController@postSetup')->name('laramultiauth.setup.post');
            Route::post('/multiauth/disable', 'TOTPController@disableMultiAuth')->name('laramultiauth.setup.disable');
        });
    }
}
