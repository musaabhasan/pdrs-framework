<?php

declare(strict_types=1);

use Pdrs\Config\AppConfig;
use Pdrs\Service\CryptoService;
use Pdrs\Service\InviteCodeService;

require __DIR__ . '/../src/bootstrap.php';

$code = (string) ($argv[1] ?? '');
$crypto = new CryptoService(AppConfig::appKey());
$inviteCodes = new InviteCodeService($crypto);
$normalized = $inviteCodes->normalize($code);

if ($normalized === '') {
    fwrite(STDERR, 'Usage: php bin/hash-invite-code.php "program-invite-code"' . PHP_EOL);
    exit(1);
}

echo $crypto->hash($normalized) . PHP_EOL;
