<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Support\Env;

final class NotificationService
{
    public function sendVerification(string $to, string $eventTitle, string $otp, string $signedLink): void
    {
        $subject = 'Verify your registration for ' . $eventTitle;
        $body = "Use this one-time code to verify your registration: {$otp}\n\n";
        $body .= "You can also open this secure verification link:\n{$signedLink}\n\n";
        $body .= "This verification will expire shortly. If you did not request it, ignore this message.";

        $this->mail($to, $subject, $body);
    }

    public function sendConfirmation(string $to, array $event, string $icsUrl): void
    {
        $subject = 'Registration confirmed: ' . $event['title'];
        $body = "Your registration has been confirmed for {$event['title']}.\n\n";
        $body .= "Calendar invite: {$icsUrl}\n\n";
        $body .= "Further joining instructions will be shared by the program team.";

        $this->mail($to, $subject, $body);
    }

    public function sendCredentialsNotice(string $to, array $event): void
    {
        $subject = 'Learning platform access for ' . $event['title'];
        $body = "Your learning platform account and course access have been prepared for {$event['title']}.\n";
        $body .= "If you already have an account, the enrollment has been linked to your existing identity.";

        $this->mail($to, $subject, $body);
    }

    private function mail(string $to, string $subject, string $body): void
    {
        $headers = sprintf(
            "From: %s <%s>\r\nContent-Type: text/plain; charset=UTF-8",
            Env::get('SMTP_FROM_NAME', 'Professional Development Office'),
            Env::get('SMTP_FROM_ADDRESS', 'registrations@example.ac.ae')
        );

        if ((string) Env::get('APP_ENV', 'local') === 'local') {
            file_put_contents(
                __DIR__ . '/../../storage/logs/mail.log',
                sprintf("[%s] TO:%s SUBJECT:%s\n%s\n\n", gmdate('c'), $to, $subject, $body),
                FILE_APPEND
            );
            return;
        }

        mail($to, $subject, $body, $headers);
    }
}
