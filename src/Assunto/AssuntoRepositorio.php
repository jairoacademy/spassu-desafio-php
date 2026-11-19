<?php
declare(strict_types=1);

namespace App\Assunto;

use App\Nucleo\Repositorio;

final readonly class AssuntoRepositorio extends Repositorio
{
    protected const string TABELA = 'Assunto';
    protected const string PK = 'CodAssunto';

    public function descricaoExistente(string $descricao, int|string $ignorarCodigo = 0): bool
    {
        $sql = 'SELECT 1 FROM Assunto WHERE Descricao = ? AND CodAssunto <> ? LIMIT 1';

        return $this->banco->consultar($sql, [$descricao, $ignorarCodigo]) !== [];
    }
}
