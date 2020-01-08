<?php

namespace RSpeekenbrink\LaraMultiAuth\Factories;

use Exception;
use RSpeekenbrink\LaraMultiAuth\Models\TOTPToken;

class TOTPTokenFactory
{
    /**
     * Create a new TOTPToken.
     *
     * @param mixed $userId
     * @return TOTPToken
     * @throws Exception
     */
    public function make($userId)
    {
        $totpToken = new TOTPToken([
            'user_id' => $userId
        ]);

        $totpToken->generateToken();

        return $totpToken;
    }
}
