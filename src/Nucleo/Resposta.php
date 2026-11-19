<?php
declare(strict_types=1);

namespace App\Nucleo;

final readonly class Resposta
{
    public function __construct(
        public int $status,
        public string $corpo = '',
        public array $cabecalhos = [],
    ) {
    }

    public static function html(string $corpo, int $status = 200): self
    {
        return new self($status, $corpo, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public static function json(mixed $dados, int $status = 200): self
    {
        $corpo = json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return new self($status, $corpo, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public static function redirecionar(string $caminho): self
    {
        return new self(303, cabecalhos: ['Location' => $caminho]);
    }

    public function enviar(): void
    {
        http_response_code($this->status);

        foreach ($this->cabecalhos as $nome => $valor) {
            header("{$nome}: {$valor}");
        }

        echo $this->corpo;
    }
}
