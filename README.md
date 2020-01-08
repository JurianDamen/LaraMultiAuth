# LaraMultiAuth
[![Build Status](https://travis-ci.org/RSpeekenbrink/LaraMultiAuth.svg?branch=master)](https://travis-ci.org/RSpeekenbrink/LaraMultiAuth)
[![Latest Stable Version](https://poser.pugx.org/rspeekenbrink/laramultiauth/version)](https://packagist.org/packages/rspeekenbrink/laramultiauth)
[![License](https://poser.pugx.org/rspeekenbrink/laramultiauth/license.png)](LICENSE)

LaraMultiAuth aims to provide the easiest interfaces and hooks to add Multi Factor 
Authentication to your Laravel Application.

## Requirements
- PHP >= 7.1
- Laravel Framework >= 5.6.0

## Installation
Require this package with composer.

```bash
composer require rspeekenbrink/laramultiauth
```

Since Laravel 5.5 Package Auto-Discovery exists so it doesn't require you to add the ServiceProvider
to your app config anymore.

If you don't use auto-discovery, add the SerivceProvider to the providers array in config/app.php

```php
RSpeekenbrink\LaraMultiAuth\ServiceProvider::class,
```

If you want to use the LaraMultiAuth facade, add this to your facades in app.php:
```php
'LaraMultiAuth' => RSpeekenbrink\LaraMultiAuth\Facade::class
```

Copy the views and migration to your local application with the publishing command:
```bash
php artisan vendor:publish --provider="RSpeekenbrink\LaraMultiAuth\ServiceProvider"
```

Then run the migrations by executing the following command:

```bash
php artisan migrate
```

If you haven't already, make the default laravel Auth with the following command:
```bash
php artisan make:auth
```

Then to the LoginController.php add the following function:
```php
use RSpeekenbrink\LaraMultiAuth\LaraMultiAuth;

...

/**
 * The user has been authenticated.
 *
 * @param  Request $request
 * @param  mixed  $user
 * @return mixed
 */
public function authenticated(Request $request, $user)
{
    return LaraMultiAuth::handle($request, $user);
}
```

And add the HasTOTPAuth trait to your User model:

```php
use RSpeekenbrink\LaraMultiAuth\HasTOTPAuth;

...

class User extends Authenticatable
{
    use HasTOTPAuth;
```

And register the routes by adding this to routes/web.php:

```php
\RSpeekenbrink\LaraMultiAuth\LaraMultiAuth::routes();
```

## Usage

### Routes
| Method | Route              | Controller                                                                    | Description                                                                       |
|--------|--------------------|-------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| GET    | /login/multiauth   | \RSpeekenbrink\LaraMultiAuth\Http\Controllers\TOTPController@showTokenScreen  | Show the multi authentication login screen                                        |
| POST   | /login/multiauth   | \RSpeekenbrink\LaraMultiAuth\Http\Controllers\TOTPController@verifyToken      | Verify posted token from multi authentication login screen                        |
| GET    | /multiauth/setup   | \RSpeekenbrink\LaraMultiAuth\Http\Controllers\TOTPController@showSetup        | (Requires user to be logged in) Shows screen to setup TOTP Authentication         |
| POST   | /multiauth/setup   | \RSpeekenbrink\LaraMultiAuth\Http\Controllers\TOTPController@postSetup        | (Requires user to be logged in) Verifies and sets up TOTP Authentication for user |
| POST   | /multiauth/disable | \RSpeekenbrink\LaraMultiAuth\Http\Controllers\TOTPController@disableMultiAuth | (Requires user to be logged in) Disable TOTP Authentication for user              |


### Disabling on Runtime
To disable Multi Auth you can always call ``LaraMultiAuth::disable()`` before the handle function.

### Set custom redirect route
You can set the route users will get redirected to after login by calling:
```php
LaraMultiAuth::setRedirectPath('/dashboard')
```

where the ``/dashboard`` part is the path to redirect to.

### Generating QR-Codes
For setting up TOTP authentication its often handy to be able to scan QR codes containing the secret than having to retype the secret on your device.
This is easily achieveable, all you need is the [Bacon QR Code Package](https://github.com/Bacon/BaconQrCode) (> v2.0.0) and the [Imagick](https://www.php.net/manual/en/book.imagick.php) php extension. Once this package is 
installed the setup controller will automatically pass an inline QR code src to the setup view.

### Custom Routes/Controller
Ofcourse it's possible to create your own routes and controllers. For that simply set the route for token verification via:
```php
LaraMultAuth::setTOTPRoute('your.route.name');
```

Source for verifying tokens and setting/removing tokens can be found in the [TOTPController](src/Http/Controllers/TOTPController.php).

## Testing
After installing the composer dev requirements the application can be tested by executing the following command from the project root directory:

```bash
vendor/bin/phpunit
```

## Todo
There is still a lot to be done for this package. The aim for this package is to add multiple ways
of multi authenticating your users. Now it only has the TOTP service available which allows users to enable
Multi Authentication with an app like Google Authenticator or Authy. Future plans are to extend the package to support
SMS services aswell and to make the implementation of Multi Authentication even easier for developers.
