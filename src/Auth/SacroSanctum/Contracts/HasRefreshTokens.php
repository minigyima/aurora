<?php

namespace Minigyima\Aurora\Auth\SacroSanctum\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Minigyima\Aurora\Auth\SacroSanctum\Models\NewRefreshToken;
use Minigyima\Aurora\Auth\SacroSanctum\Models\RefreshToken;
use Str;

/**
 * Trait for including RefreshTokens on a model
 * @method morphMany(string $class, string $string)
 */
trait HasRefreshTokens
{
    /**
     * Generates a new RefreshToken
     *
     * _Basically the same standard as Laravel Sanctum._
     *
     * @param string $name
     * @return NewRefreshToken
     */
    public function createRefreshToken(string $name): NewRefreshToken
    {
        $plainToken = Str::random(40);

        $token = $this->refreshTokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainToken),
            'expires_at' => time() + config('aurora.refresh_token_expiration'),
        ]);

        return new NewRefreshToken($token, $token->getKey() . '|' . $plainToken);
    }

    /**
     * HasMany relation for the model and its tokens
     *
     * @return MorphMany
     * @see Model::morphMany()
     */
    public function refreshTokens(): MorphMany
    {
        return $this->morphMany(RefreshToken::class, 'refreshable');
    }
}
