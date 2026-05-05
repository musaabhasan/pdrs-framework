<?php

declare(strict_types=1);

namespace Pdrs\Http;

final class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [$method, $this->compile($pattern), $handler];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as [$method, $regex, $handler]) {
            if ($method !== $request->method) {
                continue;
            }

            if (preg_match($regex, $request->path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $handler($request, $params);
            }
        }

        return Response::json(['message' => 'Route not found'], 404);
    }

    private function compile(string $pattern): string
    {
        $pattern = '/' . trim($pattern, '/');
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);

        return '#^' . $regex . '$#';
    }
}
