<?php

namespace Minigyima\Aurora\Auth\SacroSanctum\Models;

use App\Helpers\SacroSanctum\Errors\RefreshTokenExpiredException;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RefreshToken - For regenerating expired tokens
 */
class RefreshToken extends Model
{
    protected $fillable = ['name', 'token', 'expires_at', 'metadata'];

    protected $hidden = ['token'];

    protected $casts = [
        'expires_at' => 'datetime',
        'metadata' => AsArrayObject::class,
    ];

    /**
     * Finds the token in the database
     *
     * _Laravel Sanctum style_
     *
     * @param string $plainToken
     * @return void
     */
    public static function findToken(string $plainToken)
    {
        if (strpos($plainToken, '|') === false) {
            return self::where('token', hash('sha256', $plainToken))->first();
        }

        [$id, $token] = explode('|', $plainToken, 2);

        if ($instance = self::find($id)) {
            return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
        }
    }

    public function refreshable(): BelongsTo
    {
        return $this->morphTo('refreshable');
    }

    public function tryUsing(): bool
    {
        if ($this->expires_at < time()) {
            throw new RefreshTokenExpiredException();
        }

        $this->delete();

        return true;
    }
}
