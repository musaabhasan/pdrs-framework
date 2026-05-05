<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

use Pdrs\Service\CryptoService;

$key = random_bytes(32);
$crypto = new CryptoService($key);
$cipher = $crypto->encrypt('person@example.ac.ae');
$plain = $crypto->decrypt($cipher);

if ($plain !== 'person@example.ac.ae') {
    fwrite(STDERR, "Crypto round-trip failed\n");
    exit(1);
}

if ($crypto->hash('Test@Example.AC.AE') !== $crypto->hash('test@example.ac.ae')) {
    fwrite(STDERR, "Email hash normalization failed\n");
    exit(1);
}

echo "self-test-ok\n";
