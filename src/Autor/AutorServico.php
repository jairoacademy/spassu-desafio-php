<?php
declare(strict_types=1);

namespace App\Autor;

use App\Nucleo\ErroAplicacao;

final readonly class AutorServico
{
    private const int TAMANHO_NOME = 40;

    public function __construct(private AutorRepositorio $autores)
    {
    }

    public function listar(int $limite = 20, int $pagina = 1): array
    {
        return $this->autores->listar($limite, $pagina);
    }

    public function contar(): int
    {
        return $this->autores->contar();
    }

    public function buscar(int $codigo): array
    {
        return $this->autores->buscar($codigo) ?? throw ErroAplicacao::naoEncontrado('Autor');
    }

    public function criar(string $nome): array
    {
        return $this->autores->criar(['Nome' => $this->validarNome($nome)]);
    }

    public function alterar(int $codigo, string $nome): array
    {
        $this->buscar($codigo);

        return $this->autores->alterar($codigo, ['Nome' => $this->validarNome($nome, $codigo)]);
    }

    public function excluir(int $codigo): void
    {
        $this->buscar($codigo);

        try {
            $this->autores->excluir($codigo);
        } catch (ErroAplicacao $erro) {
            if ($erro->status !== 409) {
                throw $erro;
            }

            throw new ErroAplicacao('Autor vinculado a um ou mais livros.', 409, causa: $erro);
        }
    }

    private function validarNome(string $nome, int $ignorarCodigo = 0): string
    {
        $nome = trim($nome);

        $erro = match (true) {
            $nome === '' => 'Informe o nome.',
            mb_strlen($nome) > self::TAMANHO_NOME => 'O nome deve ter no máximo 40 caracteres.',
            $this->autores->nomeExistente($nome, $ignorarCodigo) => 'Já existe um autor com esse nome.',
            default => null,
        };

        if ($erro !== null) {
            throw ErroAplicacao::validacao(['nome' => $erro]);
        }

        return $nome;
    }
}
