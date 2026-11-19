<?php
declare(strict_types=1);

namespace App\Nucleo;

use RuntimeException;
use Throwable;

final class ErroAplicacao extends RuntimeException
{
    public function __construct(
        string $mensagem,
        public readonly int $status,
        public readonly array $erros = [],
        ?Throwable $causa = null,
    ) {
        parent::__construct($mensagem, previous: $causa);
    }

    public static function validacao(array $erros): self
    {
        return new self('Dados inválidos.', 422, $erros);
    }

    public static function naoEncontrado(string $recurso): self
    {
        return new self("{$recurso} inexistente.", 404);
    }
}
