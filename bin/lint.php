<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    $root . '/public',
    $root . '/src',
    $root . '/bin',
];

$failed = false;

foreach ($paths as $path) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $command = sprintf('%s -l %s', escapeshellarg(PHP_BINARY), escapeshellarg($file->getPathname()));
        passthru($command, $code);

        if ($code !== 0) {
            $failed = true;
        }
    }
}

exit($failed ? 1 : 0);
