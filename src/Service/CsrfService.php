<?php

declare(strict_types=1);

namespace Pdrs\Service;

use Pdrs\Http\Request;
use Pdrs\Support\View;

final class CsrfService
{
    private const SESSION_KEY = '_csrf_token';

    public function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[self::SESSION_KEY];
    }

    public function field(): string
    {
        $token = View::e($this->token());

        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    public function validate(Request $request): bool
    {
        $expected = (string) ($_SESSION[self::SESSION_KEY] ?? '');
        $actual = (string) $request->input('_csrf_token', '');

        return $expected !== '' && $actual !== '' && hash_equals($expected, $actual);
    }
}
