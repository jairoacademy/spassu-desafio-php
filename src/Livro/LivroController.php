<?php
declare(strict_types=1);

namespace App\Livro;

use App\Nucleo\ErroAplicacao;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;

final readonly class LivroController
{
    private const int POR_PAGINA = 20;

    public function __construct(
        private LivroServico $servico,
        private View $view,
        private Sessao $sessao,
    ) {
    }

    public function listar(Requisicao $requisicao): Resposta
    {
        $pagina = $requisicao->pagina();

        return $this->view->pagina('livros/lista', [
            'titulo' => 'Livros',
            'livros' => $this->servico->listar(self::POR_PAGINA, $pagina),
            'pagina' => $pagina,
            'totalPaginas' => max(1, (int) ceil($this->servico->contar() / self::POR_PAGINA)),
        ]);
    }

    public function novo(): Resposta
    {
        return $this->formulario('Novo livro', '/livros', [
            'titulo' => '',
            'editora' => '',
            'edicao' => '',
            'ano' => '',
            'valor' => '',
            'autores' => [],
            'assuntos' => [],
        ]);
    }

    public function editar(Requisicao $requisicao, string $id): Resposta
    {
        $livro = $this->servico->buscar((int) $id);

        return $this->formulario('Editar livro', "/livros/{$id}", [
            'titulo' => $livro['Titulo'],
            'editora' => $livro['Editora'],
            'edicao' => (string) $livro['Edicao'],
            'ano' => $livro['AnoPublicacao'],
            'valor' => number_format((float) $livro['Valor'], 2, ',', '.'),
            'autores' => array_map(strval(...), $livro['Autores']),
            'assuntos' => array_map(strval(...), $livro['Assuntos']),
        ]);
    }

    public function criar(Requisicao $requisicao): Resposta
    {
        $livro = $this->dadosDoFormulario($requisicao);

        try {
            $this->servico->criar($livro);
        } catch (ErroAplicacao $erro) {
            return $this->formularioComErro($erro, 'Novo livro', '/livros', $livro);
        }

        $this->sessao->avisar('Livro cadastrado.');

        return Resposta::redirecionar('/livros');
    }

    public function alterar(Requisicao $requisicao, string $id): Resposta
    {
        $livro = $this->dadosDoFormulario($requisicao);

        try {
            $this->servico->alterar((int) $id, $livro);
        } catch (ErroAplicacao $erro) {
            return $this->formularioComErro($erro, 'Editar livro', "/livros/{$id}", $livro);
        }

        $this->sessao->avisar('Livro alterado.');

        return Resposta::redirecionar('/livros');
    }

    public function excluir(Requisicao $requisicao, string $id): Resposta
    {
        $this->servico->excluir((int) $id);
        $this->sessao->avisar('Livro excluído.');

        return Resposta::redirecionar('/livros');
    }

    private function dadosDoFormulario(Requisicao $requisicao): array
    {
        return [
            'titulo' => $requisicao->texto('titulo'),
            'editora' => $requisicao->texto('editora'),
            'edicao' => $requisicao->texto('edicao'),
            'ano' => $requisicao->texto('ano'),
            'valor' => $requisicao->texto('valor'),
            'autores' => $requisicao->lista('autores'),
            'assuntos' => $requisicao->lista('assuntos'),
        ];
    }

    private function formularioComErro(ErroAplicacao $erro, string $titulo, string $acao, array $livro): Resposta
    {
        if ($erro->status !== 422) {
            throw $erro;
        }

        return $this->formulario($titulo, $acao, $livro, $erro->erros, 422);
    }

    private function formulario(
        string $titulo,
        string $acao,
        array $livro,
        array $erros = [],
        int $status = 200,
    ): Resposta {
        [$autores, $assuntos] = $this->servico->opcoes();

        return $this->view->pagina('livros/formulario', [
            'titulo' => $titulo,
            'acao' => $acao,
            'livro' => $livro,
            'autores' => $autores,
            'assuntos' => $assuntos,
            'erros' => $erros,
        ], $status);
    }
}
