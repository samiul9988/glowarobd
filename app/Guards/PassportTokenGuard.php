<?php

namespace App\Guards;

use Carbon\Carbon;
use Laravel\Passport\Guards\TokenGuard;

class PassportTokenGuard extends TokenGuard
{
    /**
     * {@inheritdoc}
     *
     * Extends the default Passport TokenGuard to also reject tokens that are
     * expired or revoked according to the oauth_access_tokens database record.
     * Passport's ResourceServer only validates the JWT signature and its own
     * exp claim (set at token issuance time), not the DB-level expires_at that
     * is manually updated after token creation.
     */
    protected function authenticateViaBearerToken($request): mixed
    {
        $user = parent::authenticateViaBearerToken($request);

        if (! $user) {
            return null;
        }

        $token = $user->token();

        if (! $token || $token->revoked || ($token->expires_at && Carbon::parse($token->expires_at)->isPast())) {
            return null;
        }

        return $user;
    }
}
