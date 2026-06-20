<?php

namespace App\Http\Controllers\Api;

use App\Models\DistributorProfile;
use App\Models\User;
use App\Services\FirebaseAuthService;
use App\Services\PhoneOtpService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $role = $user->getRoleNames()->first();

        if (! in_array($role, ['customer', 'distributor'], true)) {
            return $this->jsonError('This account cannot access the mobile app.', 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->jsonSuccess([
            'token' => $token,
            'user' => $this->userPayload($user->load(['distributorProfile', 'assignedDistributor'])),
        ], 'Logged in successfully.');
    }

    public function firebaseLogin(Request $request, FirebaseAuthService $firebaseAuth): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
            'account_type' => ['required', 'in:customer,distributor'],
        ]);

        $claims = $firebaseAuth->verifyIdToken($validated['id_token']);
        $firebaseUid = isset($claims['sub']) ? (string) $claims['sub'] : null;
        $firebasePhone = isset($claims['phone_number']) ? (string) $claims['phone_number'] : null;
        $firebaseEmail = isset($claims['email']) ? strtolower((string) $claims['email']) : null;

        if ($firebasePhone === null && $firebaseEmail === null) {
            return $this->jsonError('Could not verify your account with Firebase.', 422);
        }

        $user = $firebasePhone !== null
            ? $this->findUserByFirebaseIdentity($firebaseUid, $firebasePhone)
            : $this->findUserByFirebaseEmailIdentity($firebaseUid, $firebaseEmail);

        if (! $user) {
            return $this->jsonError(
                $firebasePhone !== null
                    ? 'No account found for this phone number. Please sign up first.'
                    : 'No account found for this Google email. Please sign up first.',
                404,
            );
        }

        if ($firebaseUid !== null && $user->firebase_uid !== $firebaseUid) {
            $user->update(['firebase_uid' => $firebaseUid]);
        }

        return $this->loginFirebaseUser($user, $validated['account_type']);
    }

    public function checkPhoneLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'account_type' => ['required', 'in:customer,distributor'],
        ]);

        $user = $this->findUserByPhone($validated['phone']);

        if (! $user) {
            return $this->jsonError('No account found for this phone number. Please sign up first.', 404);
        }

        return $this->loginFirebaseUser($user, $validated['account_type'], issueToken: false);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'account_type' => ['required', 'in:customer,distributor'],
            'business_name' => ['required_if:account_type,distributor', 'nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => normalize_phone($validated['phone'] ?? null),
                'city' => $validated['city'] ?? null,
            ]);

            $user->assignRole($validated['account_type']);

            if ($validated['account_type'] === 'distributor') {
                DistributorProfile::create([
                    'user_id' => $user->id,
                    'business_name' => $validated['business_name'] ?? $validated['name'],
                    'is_approved' => false,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->jsonSuccess([
            'token' => $token,
            'user' => $this->userPayload($user->fresh(['distributorProfile', 'assignedDistributor'])),
        ], 'Account created successfully.', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonSuccess([], 'Logged out successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['distributorProfile', 'assignedDistributor']);

        return $this->jsonSuccess([
            'user' => $this->userPayload($user),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:10'],
        ]);

        $user->update($validated);

        return $this->jsonSuccess([
            'user' => $this->userPayload($user->fresh(['distributorProfile', 'assignedDistributor'])),
        ], 'Profile updated successfully.');
    }

    public function sendPhoneOtp(Request $request, PhoneOtpService $phoneOtpService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'account_type' => ['required', 'in:customer,distributor'],
        ]);

        $phoneOtpService->send($validated['phone'], $validated['account_type']);

        return $this->jsonSuccess([], 'OTP sent to your phone.');
    }

    public function verifyPhoneOtp(Request $request, PhoneOtpService $phoneOtpService): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'account_type' => ['required', 'in:customer,distributor'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $phoneOtpService->verify(
            $validated['phone'],
            $validated['account_type'],
            $validated['code'],
        );

        return $this->loginFirebaseUser($user, $validated['account_type']);
    }

    private function findUserByPhone(string $phone): ?User
    {
        $phoneVariants = phone_lookup_variants($phone);

        if ($phoneVariants === []) {
            return null;
        }

        return User::query()
            ->where(function ($query) use ($phoneVariants) {
                foreach ($phoneVariants as $variant) {
                    $query->orWhere('phone', $variant);
                }
            })
            ->first();
    }

    private function findUserByFirebaseIdentity(?string $firebaseUid, string $firebasePhone): ?User
    {
        $user = $firebaseUid
            ? User::query()->where('firebase_uid', $firebaseUid)->first()
            : null;

        if ($user) {
            return $user;
        }

        return $this->findUserByPhone($firebasePhone);
    }

    private function findUserByFirebaseEmailIdentity(?string $firebaseUid, string $firebaseEmail): ?User
    {
        $user = $firebaseUid
            ? User::query()->where('firebase_uid', $firebaseUid)->first()
            : null;

        if ($user) {
            return $user;
        }

        return User::query()->where('email', $firebaseEmail)->first();
    }

    private function loginFirebaseUser(User $user, string $accountType, bool $issueToken = true): JsonResponse
    {
        $role = $user->getRoleNames()->first();

        if (! in_array($role, ['customer', 'distributor'], true)) {
            return $this->jsonError('This account cannot access the mobile app.', 403);
        }

        if ($role !== $accountType) {
            return $this->jsonError(
                $accountType === 'customer'
                    ? 'This account belongs to a dealer. Open the Dealer App to sign in.'
                    : 'This account belongs to a customer. Open the Customer App to sign in.',
                403,
            );
        }

        if (! $issueToken) {
            return $this->jsonSuccess([], 'Account verified.');
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return $this->jsonSuccess([
            'token' => $token,
            'user' => $this->userPayload($user->load(['distributorProfile', 'assignedDistributor'])),
        ], 'Logged in successfully.');
    }

    private function loginPhoneUser(User $user, string $accountType, bool $issueToken = true): JsonResponse
    {
        return $this->loginFirebaseUser($user, $accountType, $issueToken);
    }
}
