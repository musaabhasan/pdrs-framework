<?php

declare(strict_types=1);

namespace Pdrs\Service;

final class ProgramModeService
{
    private const LABELS = [
        'synchronous' => 'Synchronous',
        'asynchronous' => 'Asynchronous',
        'self_paced' => 'Self-paced',
        'instructor_led' => 'Instructor-led',
        'facilitated' => 'Facilitated',
        'hybrid' => 'Hybrid',
        'blended' => 'Blended',
        'cohort_based' => 'Cohort-based',
        'workshop' => 'Workshop',
        'webinar' => 'Webinar',
        'microlearning' => 'Microlearning',
        'mentored' => 'Mentored',
        'assessment_based' => 'Assessment-based',
        'credentialed' => 'Credentialed',
    ];

    public function labels(array $event): array
    {
        $modes = is_array($event['program_modes'] ?? null) ? $event['program_modes'] : [];
        $labels = [];

        foreach ($modes as $mode) {
            $key = $this->key((string) $mode);
            if ($key === '') {
                continue;
            }

            $labels[$key] = self::LABELS[$key] ?? $this->humanize($key);
        }

        return array_values($labels);
    }

    public function summary(array $event): string
    {
        $labels = $this->labels($event);

        return $labels === [] ? 'Flexible delivery' : implode(', ', $labels);
    }

    private function key(string $mode): string
    {
        $mode = strtolower(trim($mode));
        $mode = preg_replace('/[\s-]+/', '_', $mode) ?? '';
        $mode = preg_replace('/[^a-z0-9_]/', '', $mode) ?? '';

        return trim($mode, '_');
    }

    private function humanize(string $mode): string
    {
        return ucwords(str_replace('_', ' ', $mode));
    }
}
