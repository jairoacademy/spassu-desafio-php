<?php
declare(strict_types=1);

namespace App\Nucleo;

final readonly class Sessao
{
    public function __construct()
    {
        if (PHP_SAPI !== 'cli') {
            session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax', 'use_strict_mode' => true]);
        }
    }

    public function token(): string
    {
        return $_SESSION['token'] ??= bin2hex(random_bytes(32));
    }

    public function tokenValido(string $token): bool
    {
        return hash_equals($this->token(), $token);
    }

    public function avisar(string $mensagem, string $tipo = 'success'): void
    {
        $_SESSION['aviso'] = ['mensagem' => $mensagem, 'tipo' => $tipo];
    }

    public function consumirAviso(): ?array
    {
        $aviso = $_SESSION['aviso'] ?? null;
        unset($_SESSION['aviso']);

        return $aviso;
    }
}
