<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Config\AppConfig;

final class IcsService
{
    public function build(array $event): string
    {
        $uid = 'pdrs-' . $event['slug'] . '@' . parse_url(AppConfig::appUrl(), PHP_URL_HOST);
        $start = $this->formatDate($event['start_at']);
        $end = $this->formatDate($event['end_at']);

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//PDRS//Professional Development Registration//EN',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . gmdate('Ymd\THis\Z'),
            'DTSTART:' . $start,
            'DTEND:' . $end,
            'SUMMARY:' . $this->escape($event['title']),
            'LOCATION:' . $this->escape((string) ($event['location'] ?? 'Online')),
            'DESCRIPTION:' . $this->escape((string) ($event['summary'] ?? 'Professional development event')),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);
    }

    private function formatDate(string $value): string
    {
        return gmdate('Ymd\THis\Z', strtotime($value));
    }

    private function escape(string $value): string
    {
        return str_replace(["\r", "\n", ',', ';'], [' ', ' ', '\,', '\;'], $value);
    }
}
