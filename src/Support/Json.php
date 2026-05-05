<?php

declare(strict_types=1);

namespace Pdrs\Support;

final class Json
{
    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    public static function decode(?string $value, mixed $default = []): mixed
    {
        if ($value === null || $value === '') {
            return $default;
        }

        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $default;
        }
    }
}
