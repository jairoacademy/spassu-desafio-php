<?php
declare(strict_types=1);

namespace App\Relatorio;

use App\Autor\AutorRepositorio;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\View;

final readonly class RelatorioController
{
    public function __construct(
        private RelatorioServico $servico,
        private AutorRepositorio $autores,
        private View $view,
    ) {
    }

    public function livrosPorAutor(Requisicao $requisicao): Resposta
    {
        $autor = (int) ($requisicao->query['autor'] ?? 0);

        return $this->view->pagina('relatorio/livros-por-autor', [
            'titulo' => 'Livros por autor',
            'relatorio' => $this->servico->agruparPorAutor($autor === 0 ? null : $autor),
            'autores' => $this->autores->todos(),
            'autorSelecionado' => $autor,
        ]);
    }
}
