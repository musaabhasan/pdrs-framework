<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Config\AppConfig;
use Pdrs\Http\Request;
use Pdrs\Repository\VerificationRepository;
use Pdrs\Support\Env;
use Pdrs\Support\Uuid;

final class VerificationService
{
    public function __construct(
        private readonly VerificationRepository $repository,
        private readonly CryptoService $crypto,
        private readonly NotificationService $notifications,
    ) {
    }

    public function issue(array $event, string $email, Request $request): array
    {
        $otp = (string) random_int(100000, 999999);
        $token = Uuid::v4() . '.' . bin2hex(random_bytes(16));
        $expiresAt = gmdate('Y-m-d H:i:s', time() + (Env::int('SIGNED_LINK_EXPIRES_MINUTES', 30) * 60));

        $this->repository->create([
            'uuid' => Uuid::v4(),
            'event_id' => $event['id'],
            'email_hash' => $this->crypto->hash($email),
            'email_encrypted' => $this->crypto->encrypt($email),
            'code_hash' => password_hash($otp, PASSWORD_DEFAULT),
            'signed_token_hash' => $this->crypto->hash($token),
            'expires_at' => $expiresAt,
            'ip_hash' => $this->crypto->hash($request->ip()),
            'user_agent_hash' => $this->crypto->hash($request->userAgent()),
        ]);

        $link = AppConfig::appUrl() . '/verify?token=' . urlencode($token);
        $this->notifications->sendVerification($email, $event['title'], $otp, $link);

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    public function verifyToken(string $token): ?array
    {
        $challenge = $this->repository->findValidByTokenHash($this->crypto->hash($token));

        if (!$challenge) {
            return null;
        }

        $this->repository->markVerified((int) $challenge['id']);
        $challenge['email'] = $this->crypto->decrypt($challenge['email_encrypted']);
        $challenge['signature'] = $this->signature($challenge);

        return $challenge;
    }

    public function verifyOtp(int $eventId, string $email, string $otp): ?array
    {
        $challenge = $this->repository->findValidByEmailHash($eventId, $this->crypto->hash($email));

        if (!$challenge) {
            return null;
        }

        $this->repository->incrementAttempts((int) $challenge['id']);

        if (!password_verify($otp, $challenge['code_hash'])) {
            return null;
        }

        $this->repository->markVerified((int) $challenge['id']);
        $challenge['email'] = $email;
        $challenge['signature'] = $this->signature($challenge);

        return $challenge;
    }

    public function verifiedChallenge(int $id, string $signature): ?array
    {
        $challenge = $this->repository->findVerifiedById($id);

        if (!$challenge || !hash_equals($this->signature($challenge), $signature)) {
            return null;
        }

        $challenge['email'] = $this->crypto->decrypt($challenge['email_encrypted']);
        $challenge['signature'] = $signature;

        return $challenge;
    }

    private function signature(array $challenge): string
    {
        return $this->crypto->sign('challenge:' . $challenge['id'] . ':' . $challenge['email_hash']);
    }
}
