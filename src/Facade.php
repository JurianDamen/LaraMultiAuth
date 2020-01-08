<?php

namespace RSpeekenbrink\LaraMultiAuth;

/**
 * LaraMultiAuth Facade Class
 *
 * @package RSpeekenbrink\LaraMultiAuth
 * @method static mixed handle(Illuminate\Http\Request $request, Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static mixed logoutAndRedirectToTokenScreen(Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static bool isMultiAuthEnabledForUser(Illuminate\Contracts\Auth\Authenticatable $user)
 * @method static \RSpeekenbrink\LaraMultiAuth\LaraMultiAuth setRedirectPath(string $path)
 * @method static void routes()
 *
 * @see \RSpeekenbrink\LaraMultiAuth\LaraMultiAuth
 */
class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return LaraMultiAuth::class;
    }
}
