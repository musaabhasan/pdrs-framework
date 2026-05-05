<?php

declare(strict_types=1);

namespace Pdrs\Support;

final class View
{
    public static function render(string $title, string $body): string
    {
        $appName = htmlspecialchars((string) Env::get('APP_NAME', 'PDRS'), ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$safeTitle} | {$appName}</title>
  <link rel="stylesheet" href="/assets/app.css?v=2">
</head>
<body>
  <header class="topbar">
    <a class="brand" href="/" aria-label="Professional Development Registration System home">
      <span class="brand-mark">PD</span>
      <span>Professional Development Registration</span>
    </a>
    <span class="topbar-badge">Secure enrollment</span>
  </header>
  <main class="page-shell">
    {$body}
  </main>
</body>
</html>
HTML;
    }

    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
