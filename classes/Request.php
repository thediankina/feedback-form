<?php

namespace app;

final class Request
{
    private array $get;
    private array $post;

    public function __construct(?array $get = null, ?array $post = null)
    {
        $this->get = $get ?? ($_GET ?? []);
        $this->post = $post ?? ($_POST ?? []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    public function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
