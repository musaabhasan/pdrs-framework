<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Throwable;
use Pdrs\Repository\RegistrationRepository;
use Pdrs\Support\Env;

final class ProvisioningService
{
    public function __construct(
        private readonly RegistrationRepository $registrations,
        private readonly FieldMapper $mapper,
        private readonly MoodleClient $moodle,
        private readonly NotificationService $notifications,
    ) {
    }

    public function provision(int $registrationId, array $event, array $registration): bool
    {
        try {
            [$user] = $this->mapper->moodleUser($event, $registration);
            $existing = $this->moodle->findUserByEmailOrUsername($registration['email'], $user['username']);
            $moodleUserId = $existing ? (int) $existing['id'] : $this->moodle->createUser($user);

            $courseIds = array_map('intval', $event['moodle_course_ids'] ?? []);
            $cohortIds = array_map('intval', $event['moodle_cohort_ids'] ?? []);
            $this->moodle->enrolUser($moodleUserId, $courseIds, Env::int('MOODLE_STUDENT_ROLE_ID', 5));
            $this->moodle->addToCohorts($moodleUserId, $cohortIds);
            $this->registrations->markProvisioned($registrationId, $moodleUserId);

            if (!$existing) {
                $this->notifications->sendCredentialsNotice($registration['email'], $event);
            }

            return true;
        } catch (Throwable $exception) {
            $this->registrations->markFailed($registrationId, $exception->getMessage());
            return false;
        }
    }
}
