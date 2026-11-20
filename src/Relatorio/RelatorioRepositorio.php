<?php
declare(strict_types=1);

namespace App\Relatorio;

use App\Nucleo\Banco;

final readonly class RelatorioRepositorio
{
    public function __construct(private Banco $banco)
    {
    }

    public function livrosPorAutor(?int $codigoAutor = null): array
    {
        if ($codigoAutor === null) {
            return $this->banco->consultar('SELECT * FROM vw_livros_por_autor ORDER BY Autor, Titulo');
        }

        return $this->banco->consultar(
            'SELECT * FROM vw_livros_por_autor WHERE CodAutor = ? ORDER BY Autor, Titulo',
            [$codigoAutor],
        );
    }
}
