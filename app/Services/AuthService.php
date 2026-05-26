<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\Auth\LoginData;
use App\Data\Auth\RegisterData;
use App\Enums\UserStatus;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Traits\Loggable;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    use Loggable;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function register(RegisterData $data): array
    {
        $this->logStart('user_registration', ['email' => $data->email]);

        try {
            $user = $this->userRepository->create([
                'name'     => $data->name,
                'email'    => $data->email,
                'password' => Hash::make($data->password),
                'city'     => $data->city,
                'status'   => UserStatus::ACTIVE->value,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            $this->logSuccess('user_registration', ['user_id' => $user->id]);

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            $this->logFailure('user_registration', $e, ['email' => $data->email]);
            throw $e;
        }
    }

    public function login(LoginData $data): array
    {
        $this->logStart('user_login', ['email' => $data->email]);

        try {
            $user = $this->userRepository->findByEmail($data->email);

            if (! $user || ! Hash::check($data->password, $user->password)) {
                throw new ApiException('Invalid credentials provided.', 401);
            }

            if ($user->status === UserStatus::BANNED) {
                throw new ApiException('Your account has been banned.', 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            $this->logSuccess('user_login', ['user_id' => $user->id]);

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Throwable $e) {
            $this->logFailure('user_login', $e, ['email' => $data->email]);
            throw $e;
        }
    }

    public function logout(User $user): void
    {
        $this->logStart('user_logout', ['user_id' => $user->id]);

        try {
            $user->currentAccessToken()->delete();
            $this->logSuccess('user_logout', ['user_id' => $user->id]);
        } catch (\Throwable $e) {
            $this->logFailure('user_logout', $e, ['user_id' => $user->id]);
            throw $e;
        }
    }
}
