<?php
declare(strict_types=1);

namespace App\Autor;

use App\Nucleo\Repositorio;

final readonly class AutorRepositorio extends Repositorio
{
    protected const string TABELA = 'Autor';
    protected const string PK = 'CodAutor';

    public function nomeExistente(string $nome, int|string $ignorarCodigo = 0): bool
    {
        $sql = 'SELECT 1 FROM Autor WHERE Nome = ? AND CodAutor <> ? LIMIT 1';

        return $this->banco->consultar($sql, [$nome, $ignorarCodigo]) !== [];
    }
}
