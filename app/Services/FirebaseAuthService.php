<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    private ?FirebaseAuth $auth = null;

    /**
     * @return array<string, mixed>
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verified = $this->auth()->verifyIdToken($idToken);

            return $verified->claims()->all();
        } catch (FailedToVerifyToken $exception) {
            Log::warning('Firebase token verification failed', [
                'message' => $exception->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'id_token' => ['Invalid or expired OTP session. Please try again.'],
            ]);
        }
    }

    private function auth(): FirebaseAuth
    {
        if ($this->auth instanceof FirebaseAuth) {
            return $this->auth;
        }

        $credentials = config('firebase.credentials');

        if (! is_string($credentials) || $credentials === '' || ! is_readable($credentials)) {
            throw ValidationException::withMessages([
                'id_token' => ['Phone login is not configured on the server yet.'],
            ]);
        }

        $factory = (new Factory)->withServiceAccount($credentials);
        $this->auth = $factory->createAuth();

        return $this->auth;
    }
}
