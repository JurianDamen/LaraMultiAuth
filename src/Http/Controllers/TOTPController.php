<?php

namespace RSpeekenbrink\LaraMultiAuth\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use RSpeekenbrink\LaraMultiAuth\LaraMultiAuth;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;
use RSpeekenbrink\LaraMultiAuth\Services\TOTPService;

class TOTPController extends BaseController
{
    /**
     * Return the tokenscreen view if id of user is in session otherwise
     * redirect to login.
     *
     * @param Request $request
     * @return mixed
     */
    public function showTokenScreen(Request $request)
    {
        return $request->session()->has('laramultiauth.id') ? view('laramultiauth.multiauth') : redirect('login');
    }

    /**
     * Show the multiauth setup screen.
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function showSetup(Request $request)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = $request->user();

        if (LaraMultiAuth::isMultiAuthEnabledForUser($user)) {
            return view('laramultiauth.setup', ['alreadySetup' => true]);
        }

        if (Session::has('laramultiauth.token')) {
            $token = Session::get('laramultiauth.token');
        } else {
            $token = $user->createTotpToken()->token;
            Session::put('laramultiauth.token', $token);
        }

        $qrCode = null;

        if (LaraMultiAuth::checkIfQRGenerationIsAvailable()) {
            $qrCode = TOTPService::getInlineQRCode(config('app.name'), '', $token);
        }

        return view('laramultiauth.setup', ['token' => $token, 'qrCode' => $qrCode]);
    }

    /**
     * Handle the setup verification.
     *
     * @param Request $request
     * @return mixed
     */
    public function postSetup(Request $request)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $codeLength = TOTPService::getCodeLength();

        $request->validate([
            'token' => 'required|string|max:' . $codeLength . '|min:' . $codeLength,
            'secret' => 'required|string'
        ]);

        if (TOTPService::verifyCode($request->secret, $request->token)) {
            $user = $request->user();

            $token = new TOTPToken();
            $token->user()->associate($user);
            $token->token = $request->secret;
            $token->save();

            Session::forget('laramultiauth.token');

            return back();
        }

        return back()->withErrors(['token' => 'Invalid Token']);
    }

    /**
     * Verify posted code against user
     *
     * @param Request $request
     * @return mixed
     */
    public function verifyToken(Request $request)
    {
        if (!$request->session()->has('laramultiauth.id')) {
            return redirect('login');
        }

        $codeLength = TOTPService::getCodeLength();

        $request->validate([
            'token' => 'required|string|max:' . $codeLength . '|min:' . $codeLength,
        ]);

        $model = config('auth.providers.' . config('auth.guards.api.provider') . '.model');

        $user = (new $model)->findOrFail(
            $request->session()->get('laramultiauth.id')
        );

        if (LaraMultiAuth::isMultiAuthEnabledForUser($user)) {
            if (TOTPService::verifyCode($user->totpToken->token, $request->token)) {
                Auth::login($user);

                $request->session()->forget('laramultiauth');

                return redirect()->intended(LaraMultiAuth::$redirectPath);
            }
        } else {
            return redirect('login');
        }

        return back()->withErrors(['token' => 'Invalid Token']);
    }

    /**
     * Disable MultiAuth for current logged in user.
     *
     * @param Request $request
     * @return mixed
     */
    public function disableMultiAuth(Request $request)
    {
        if (Auth::check() && $user = $request->user()) {
            if (LaraMultiAuth::isMultiAuthEnabledForUser($user)) {
                $token = $user->totpToken;
                $token->delete();
            }
        }

        return back();
    }
}
