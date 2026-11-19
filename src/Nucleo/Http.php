<?php
declare(strict_types=1);

namespace App\Nucleo;

use Closure;
use Throwable;

final readonly class Http
{
    public function __construct(
        private Closure $montarRotas,
        private Sessao $sessao,
        private View $view,
    ) {
    }

    public function atender(): void
    {
        $this->processar(Requisicao::capturar())->enviar();
    }

    public function processar(Requisicao $requisicao): Resposta
    {
        try {
            $this->exigirToken($requisicao);

            return $this->executarRota($requisicao);
        } catch (ErroAplicacao $erro) {
            return $this->paginaDeErro($erro->status, $erro->getMessage());
        } catch (Throwable $erro) {
            error_log((string) $erro);

            return $this->paginaDeErro(500, 'Não foi possível concluir a operação.');
        }
    }

    private function exigirToken(Requisicao $requisicao): void
    {
        if ($requisicao->metodo === 'POST' && !$this->sessao->tokenValido($requisicao->texto('_token'))) {
            throw new ErroAplicacao('Sessão expirada. Recarregue a página e tente novamente.', 403);
        }
    }

    private function executarRota(Requisicao $requisicao): Resposta
    {
        foreach (($this->montarRotas)() as $rota => $acao) {
            $padrao = '#^' . str_replace('{id}', '(\d+)', $rota) . '$#';

            if (preg_match($padrao, "{$requisicao->metodo} {$requisicao->caminho}", $partes)) {
                return $acao($requisicao, ...array_slice($partes, 1));
            }
        }

        throw ErroAplicacao::naoEncontrado('Página');
    }

    private function paginaDeErro(int $status, string $mensagem): Resposta
    {
        return $this->view->pagina('erro', [
            'titulo' => "Erro {$status}",
            'status' => $status,
            'mensagem' => $mensagem,
        ], $status);
    }
}
