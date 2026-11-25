<?php
declare(strict_types=1);

namespace App\Assunto;

use App\Nucleo\ErroAplicacao;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;

final readonly class AssuntoController
{
    private const int POR_PAGINA = 10;

    public function __construct(
        private AssuntoServico $servico,
        private View $view,
        private Sessao $sessao,
    ) {
    }

    public function listar(Requisicao $requisicao): Resposta
    {
        $pagina = $requisicao->pagina();
        $termo = mb_substr($requisicao->parametro('q'), 0, 100);

        return $this->view->pagina('assuntos/lista', [
            'titulo' => 'Assuntos',
            'termo' => $termo,
            'assuntos' => $this->servico->listar(self::POR_PAGINA, $pagina, $termo),
            'paginacao' => [
                'rota' => '/assuntos',
                'rotulo' => 'assuntos',
                'pagina' => $pagina,
                'porPagina' => self::POR_PAGINA,
                'total' => $this->servico->contar($termo),
                'filtros' => ['q' => $termo],
            ],
        ]);
    }

    public function novo(): Resposta
    {
        return $this->formulario('Novo assunto', '/assuntos');
    }

    public function editar(Requisicao $requisicao, string $id): Resposta
    {
        $assunto = $this->servico->buscar((int) $id);

        return $this->formulario('Editar assunto', "/assuntos/{$id}", $assunto['Descricao']);
    }

    public function criar(Requisicao $requisicao): Resposta
    {
        try {
            $this->servico->criar($requisicao->texto('descricao'));
        } catch (ErroAplicacao $erro) {
            $descricao = $requisicao->texto('descricao');

            return $this->formularioComErro($erro, 'Novo assunto', '/assuntos', $descricao);
        }

        $this->sessao->avisar('Assunto cadastrado.');

        return Resposta::redirecionar('/assuntos');
    }

    public function alterar(Requisicao $requisicao, string $id): Resposta
    {
        try {
            $this->servico->alterar((int) $id, $requisicao->texto('descricao'));
        } catch (ErroAplicacao $erro) {
            $descricao = $requisicao->texto('descricao');

            return $this->formularioComErro($erro, 'Editar assunto', "/assuntos/{$id}", $descricao);
        }

        $this->sessao->avisar('Assunto alterado.');

        return Resposta::redirecionar('/assuntos');
    }

    public function excluir(Requisicao $requisicao, string $id): Resposta
    {
        try {
            $this->servico->excluir((int) $id);
            $this->sessao->avisar('Assunto excluído.');
        } catch (ErroAplicacao $erro) {
            if ($erro->status !== 409) {
                throw $erro;
            }

            $this->sessao->avisar($erro->getMessage(), 'danger');
        }

        return Resposta::redirecionar('/assuntos');
    }

    private function formularioComErro(
        ErroAplicacao $erro,
        string $titulo,
        string $acao,
        string $descricao,
    ): Resposta {
        if ($erro->status !== 422) {
            throw $erro;
        }

        return $this->formulario($titulo, $acao, $descricao, $erro->erros, 422);
    }

    private function formulario(
        string $titulo,
        string $acao,
        string $descricao = '',
        array $erros = [],
        int $status = 200,
    ): Resposta {
        return $this->view->pagina('assuntos/formulario', [
            'titulo' => $titulo,
            'acao' => $acao,
            'descricao' => $descricao,
            'erros' => $erros,
        ], $status);
    }
}
