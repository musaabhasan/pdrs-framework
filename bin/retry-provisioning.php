<?php

declare(strict_types=1);

use Pdrs\Config\AppConfig;
use Pdrs\Repository\EventRepository;
use Pdrs\Repository\RegistrationRepository;
use Pdrs\Service\CryptoService;
use Pdrs\Service\FieldMapper;
use Pdrs\Service\MoodleClient;
use Pdrs\Service\NotificationService;
use Pdrs\Service\ProvisioningService;
use Pdrs\Support\Database;
use Pdrs\Support\Env;
use Pdrs\Support\Json;

require __DIR__ . '/../src/bootstrap.php';

$db = Database::connection();
$registrations = new RegistrationRepository($db);
$events = new EventRepository($db);
$crypto = new CryptoService(AppConfig::appKey());
$provisioning = new ProvisioningService(
    $registrations,
    new FieldMapper(),
    new MoodleClient(),
    new NotificationService()
);

$limit = Env::int('PROVISIONING_RETRY_LIMIT', 25);
$summary = ['attempted' => 0, 'succeeded' => 0, 'failed' => 0, 'completed_at' => gmdate('c')];

foreach ($registrations->findProvisioningRetryCandidates($limit) as $registration) {
    $event = $events->findById((int) $registration['event_id']);
    if (!$event) {
        $summary['failed']++;
        continue;
    }

    $metadata = Json::decode($crypto->decrypt($registration['metadata_encrypted']) ?? '{}', []);
    $ok = $provisioning->provision((int) $registration['id'], $event, [
        'email' => (string) $crypto->decrypt($registration['email_encrypted']),
        'first_name' => (string) $crypto->decrypt($registration['first_name_encrypted']),
        'last_name' => (string) $crypto->decrypt($registration['last_name_encrypted']),
        'city' => (string) $crypto->decrypt($registration['city_encrypted']),
        'metadata' => is_array($metadata) ? $metadata : [],
    ]);

    $summary['attempted']++;
    $summary[$ok ? 'succeeded' : 'failed']++;
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
