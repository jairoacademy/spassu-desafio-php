<?php
declare(strict_types=1);

namespace App\Nucleo;

final readonly class Requisicao
{
    public function __construct(
        public string $metodo,
        public string $caminho,
        public array $query = [],
        public array $corpo = [],
    ) {
    }

    public static function capturar(): self
    {
        $caminho = (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return new self($_SERVER['REQUEST_METHOD'], $caminho, $_GET, $_POST);
    }

    public function texto(string $campo): string
    {
        $valor = $this->corpo[$campo] ?? '';

        return is_string($valor) ? trim($valor) : '';
    }

    public function pagina(): int
    {
        return max(1, min((int) ($this->query['pagina'] ?? 1), 1_000_000));
    }
}
