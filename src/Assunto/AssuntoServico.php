<?php
declare(strict_types=1);

namespace App\Assunto;

use App\Nucleo\ErroAplicacao;

final readonly class AssuntoServico
{
    private const int TAMANHO_DESCRICAO = 20;

    public function __construct(private AssuntoRepositorio $assuntos)
    {
    }

    public function listar(int $limite = 20, int $pagina = 1, string $termo = ''): array
    {
        return $this->assuntos->listar($limite, $pagina, $termo);
    }

    public function contar(string $termo = ''): int
    {
        return $this->assuntos->contar($termo);
    }

    public function buscar(int $codigo): array
    {
        return $this->assuntos->buscar($codigo) ?? throw ErroAplicacao::naoEncontrado('Assunto');
    }

    public function criar(string $descricao): array
    {
        return $this->assuntos->criar(['Descricao' => $this->validarDescricao($descricao)]);
    }

    public function alterar(int $codigo, string $descricao): array
    {
        $this->buscar($codigo);

        return $this->assuntos->alterar($codigo, ['Descricao' => $this->validarDescricao($descricao, $codigo)]);
    }

    public function excluir(int $codigo): void
    {
        $this->buscar($codigo);

        try {
            $this->assuntos->excluir($codigo);
        } catch (ErroAplicacao $erro) {
            if ($erro->status !== 409) {
                throw $erro;
            }

            throw new ErroAplicacao('Assunto vinculado a um ou mais livros.', 409, causa: $erro);
        }
    }

    private function validarDescricao(string $descricao, int $ignorarCodigo = 0): string
    {
        $descricao = trim($descricao);

        $erro = match (true) {
            $descricao === '' => 'Informe a descrição.',
            mb_strlen($descricao) > self::TAMANHO_DESCRICAO => 'A descrição deve ter no máximo 20 caracteres.',
            $this->assuntos->descricaoExistente($descricao, $ignorarCodigo)
                => 'Já existe um assunto com essa descrição.',
            default => null,
        };

        if ($erro !== null) {
            throw ErroAplicacao::validacao(['descricao' => $erro]);
        }

        return $descricao;
    }
}
