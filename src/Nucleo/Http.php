<?php
declare(strict_types=1);

namespace App\Nucleo;

use Closure;
use Throwable;

final readonly class Http
{
    public function __construct(private Closure $montarRotas)
    {
    }

    public function atender(): void
    {
        $this->processar(Requisicao::capturar())->enviar();
    }

    public function processar(Requisicao $requisicao): Resposta
    {
        try {
            return $this->executarRota($requisicao);
        } catch (ErroAplicacao $erro) {
            return Resposta::json(['mensagem' => $erro->getMessage(), 'erros' => $erro->erros], $erro->status);
        } catch (Throwable $erro) {
            error_log((string) $erro);

            return Resposta::json(['mensagem' => 'Erro interno.'], 500);
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
}
