<?php

declare(strict_types=1);

namespace Pdrs\Service;

use RuntimeException;
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

    public function sendConfirmation(string $to, array $event, string $icsUrl, string $status = 'pending', string $reason = ''): void
    {
        $approved = in_array($status, ['approved', 'provisioned'], true);
        $subject = ($approved ? 'Registration approved: ' : 'Registration received: ') . $event['title'];
        $body = "Your registration has been received for {$event['title']}.\n\n";
        $body .= $approved
            ? "Your registration is approved and learning platform access is being prepared.\n\n"
            : "Your registration is pending review according to the event approval policy.\n\n";
        $body .= "Calendar invite: {$icsUrl}\n\n";
        $body .= "Further joining instructions will be shared by the program team.";

        if ($reason === 'existing_registration') {
            $body .= "\n\nA previous registration already exists for this event, so the original record remains active.";
        }

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
        if ((string) Env::get('APP_ENV', 'local') === 'local') {
            file_put_contents(
                __DIR__ . '/../../storage/logs/mail.log',
                sprintf("[%s] TO:%s SUBJECT:%s\n%s\n\n", gmdate('c'), $to, $subject, $body),
                FILE_APPEND
            );
            return;
        }

        $this->smtp($to, $subject, $body);
    }

    private function smtp(string $to, string $subject, string $body): void
    {
        $host = (string) Env::get('SMTP_HOST', '');
        $port = Env::int('SMTP_PORT', 587);
        $timeout = Env::int('SMTP_TIMEOUT_SECONDS', 15);
        $encryption = strtolower((string) Env::get('SMTP_ENCRYPTION', 'starttls'));
        $fromAddress = (string) Env::get('SMTP_FROM_ADDRESS', 'registrations@example.ac.ae');
        $fromName = (string) Env::get('SMTP_FROM_NAME', 'Professional Development Office');

        if ($host === '') {
            throw new RuntimeException('SMTP_HOST is not configured.');
        }

        $target = $encryption === 'tls' ? 'ssl://' . $host : $host;
        $socket = fsockopen($target, $port, $errno, $errstr, $timeout);
        if (!$socket) {
            throw new RuntimeException('SMTP connection failed: ' . $errstr);
        }

        stream_set_timeout($socket, $timeout);
        $this->expect($socket, [220]);
        $this->command($socket, 'EHLO ' . $this->hostname(), [250]);

        if ($encryption === 'starttls') {
            $this->command($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP STARTTLS negotiation failed.');
            }
            $this->command($socket, 'EHLO ' . $this->hostname(), [250]);
        }

        $username = (string) Env::get('SMTP_USERNAME', '');
        $password = (string) Env::get('SMTP_PASSWORD', '');
        if ($username !== '' && $password !== '') {
            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
        }

        $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
        $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
        $this->command($socket, 'DATA', [354]);
        $this->write($socket, $this->message($fromName, $fromAddress, $to, $subject, $body) . "\r\n.");
        $this->expect($socket, [250]);
        $this->command($socket, 'QUIT', [221]);
        fclose($socket);
    }

    /**
     * @param resource $socket
     */
    private function command($socket, string $command, array $expectedCodes): string
    {
        $this->write($socket, $command);

        return $this->expect($socket, $expectedCodes);
    }

    /**
     * @param resource $socket
     */
    private function write($socket, string $line): void
    {
        fwrite($socket, $line . "\r\n");
    }

    /**
     * @param resource $socket
     */
    private function expect($socket, array $expectedCodes): string
    {
        $response = '';

        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3}\s/', $line) === 1) {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response: ' . trim($response));
        }

        return $response;
    }

    private function message(string $fromName, string $fromAddress, string $to, string $subject, string $body): string
    {
        $headers = [
            'From: ' . $this->cleanHeader($fromName) . ' <' . $this->cleanHeader($fromAddress) . '>',
            'To: <' . $this->cleanHeader($to) . '>',
            'Subject: ' . $this->cleanHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000',
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@pdrs.local>',
        ];

        $normalizedBody = preg_replace("/\r\n|\r|\n/", "\r\n", $body) ?? $body;
        $message = implode("\r\n", $headers) . "\r\n\r\n" . $normalizedBody;

        return preg_replace('/^\./m', '..', $message) ?? $message;
    }

    private function cleanHeader(string $value): string
    {
        return trim(str_replace(["\r", "\n"], ' ', $value));
    }

    private function hostname(): string
    {
        $host = parse_url((string) Env::get('APP_URL', 'http://localhost'), PHP_URL_HOST);

        return $host ?: 'localhost';
    }
}
