<?php
declare(strict_types=1);

namespace App\Autor;

use App\Nucleo\ErroAplicacao;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
final readonly class AutorController
{
    private const int POR_PAGINA = 10;

    public function __construct(
        private AutorServico $servico,
        private View $view,
        private Sessao $sessao,
    ) {
    }

    public function listar(Requisicao $requisicao): Resposta
    {
        $pagina = $requisicao->pagina();

        return $this->view->pagina('autores/lista', [
            'titulo' => 'Autores',
            'autores' => $this->servico->listar(self::POR_PAGINA, $pagina),
            'paginacao' => [
                'rota' => '/autores',
                'rotulo' => 'autores',
                'pagina' => $pagina,
                'porPagina' => self::POR_PAGINA,
                'total' => $this->servico->contar(),
            ],
        ]);
    }

    public function novo(): Resposta
    {
        return $this->formulario('Novo autor', '/autores');
    }

    public function editar(Requisicao $requisicao, string $id): Resposta
    {
        $autor = $this->servico->buscar((int) $id);

        return $this->formulario('Editar autor', "/autores/{$id}", $autor['Nome']);
    }

    public function criar(Requisicao $requisicao): Resposta
    {
        try {
            $this->servico->criar($requisicao->texto('nome'));
        } catch (ErroAplicacao $erro) {
            return $this->formularioComErro($erro, 'Novo autor', '/autores', $requisicao->texto('nome'));
        }

        $this->sessao->avisar('Autor cadastrado.');

        return Resposta::redirecionar('/autores');
    }

    public function alterar(Requisicao $requisicao, string $id): Resposta
    {
        try {
            $this->servico->alterar((int) $id, $requisicao->texto('nome'));
        } catch (ErroAplicacao $erro) {
            return $this->formularioComErro($erro, 'Editar autor', "/autores/{$id}", $requisicao->texto('nome'));
        }

        $this->sessao->avisar('Autor alterado.');

        return Resposta::redirecionar('/autores');
    }

    public function excluir(Requisicao $requisicao, string $id): Resposta
    {
        try {
            $this->servico->excluir((int) $id);
            $this->sessao->avisar('Autor excluído.');
        } catch (ErroAplicacao $erro) {
            if ($erro->status !== 409) {
                throw $erro;
            }

            $this->sessao->avisar($erro->getMessage(), 'danger');
        }

        return Resposta::redirecionar('/autores');
    }

    private function formularioComErro(ErroAplicacao $erro, string $titulo, string $acao, string $nome): Resposta
    {
        if ($erro->status !== 422) {
            throw $erro;
        }

        return $this->formulario($titulo, $acao, $nome, $erro->erros, 422);
    }

    private function formulario(
        string $titulo,
        string $acao,
        string $nome = '',
        array $erros = [],
        int $status = 200,
    ): Resposta {
        return $this->view->pagina('autores/formulario', [
            'titulo' => $titulo,
            'acao' => $acao,
            'nome' => $nome,
            'erros' => $erros,
        ], $status);
    }
}
