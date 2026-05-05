<?php

declare(strict_types=1);

namespace Pdrs\Service;

final class ApprovalService
{
    public function evaluate(array $event, string $email, bool $paymentConfirmed = false): array
    {
        if ((int) ($event['requires_payment'] ?? 0) === 1 && !$paymentConfirmed) {
            return ['status' => 'pending', 'reason' => 'Payment confirmation is required.'];
        }

        $domains = array_map('strtolower', $event['allowed_domains'] ?? []);

        if ($domains !== []) {
            $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));

            if (!in_array($domain, $domains, true)) {
                return ['status' => 'pending', 'reason' => 'Email domain requires manual review.'];
            }
        }

        if ((int) ($event['instant_approval'] ?? 1) !== 1) {
            return ['status' => 'pending', 'reason' => 'Manual approval is enabled for this event.'];
        }

        return ['status' => 'approved', 'reason' => 'Automatic approval policy matched.'];
    }
}
