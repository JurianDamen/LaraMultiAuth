<?php

namespace RSpeekenbrink\LaraMultiAuth\Models;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use RSpeekenbrink\LaraMultiAuth\Services\TOTPService;

class TOTPToken extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'totp_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'token',
    ];

    /**
     * Get the user that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.'.config('auth.guards.api.provider').'.model'),
            'user_id'
        );
    }

    /**
     * Generate a new Token for this TOTPToken Instance.
     *
     * @return $this
     * @throws Exception
     */
    public function generateToken()
    {
        $this->token = Container::getInstance()->make(TOTPService::class)->generateSecret();

        return $this;
    }
}
