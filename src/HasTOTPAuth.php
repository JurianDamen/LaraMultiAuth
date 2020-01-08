<?php

namespace RSpeekenbrink\LaraMultiAuth;

use Exception;
use Illuminate\Container\Container;
use RSpeekenbrink\LaraMultiAuth\Factories\TOTPTokenFactory;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;

trait HasTOTPAuth
{
    /**
     * Get the TOTPToken of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function totpToken()
    {
        return $this->hasOne(TOTPToken::class, 'user_id');
    }

    /**
     * Create a new TOTPToken for the user.
     *
     * @return mixed
     * @throws Exception
     */
    public function createTotpToken()
    {
        return Container::getInstance()->make(TOTPTokenFactory::class)->make(
            $this->getKey()
        );
    }
}
