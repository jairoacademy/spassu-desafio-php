<?php
declare(strict_types=1);

namespace App\Livro;

use App\Nucleo\Repositorio;

final readonly class LivroRepositorio extends Repositorio
{
    protected const string TABELA = 'Livro';
    protected const string PK = 'CodLivro';

    public function criarComVinculos(array $dados, array $autores, array $assuntos): array
    {
        return $this->banco->transacao(function () use ($dados, $autores, $assuntos): array {
            $livro = $this->criar($dados);
            $this->vincular($livro['CodLivro'], $autores, $assuntos);

            return $livro;
        });
    }

    public function alterarComVinculos(int $codigo, array $dados, array $autores, array $assuntos): array
    {
        return $this->banco->transacao(function () use ($codigo, $dados, $autores, $assuntos): array {
            $livro = $this->alterar($codigo, $dados);
            $this->banco->executar('DELETE FROM Livro_Autor WHERE Livro_CodLivro = ?', [$codigo]);
            $this->banco->executar('DELETE FROM Livro_Assunto WHERE Livro_CodLivro = ?', [$codigo]);
            $this->vincular($codigo, $autores, $assuntos);

            return $livro;
        });
    }

    public function autores(int $codigo): array
    {
        $sql = 'SELECT Autor_CodAutor AS codigo FROM Livro_Autor WHERE Livro_CodLivro = ? ORDER BY Autor_CodAutor';

        return array_column($this->banco->consultar($sql, [$codigo]), 'codigo');
    }

    public function assuntos(int $codigo): array
    {
        $sql = 'SELECT Assunto_CodAssunto AS codigo FROM Livro_Assunto'
            . ' WHERE Livro_CodLivro = ? ORDER BY Assunto_CodAssunto';

        return array_column($this->banco->consultar($sql, [$codigo]), 'codigo');
    }

    private function vincular(int $codigo, array $autores, array $assuntos): void
    {
        foreach ($autores as $autor) {
            $this->banco->executar('INSERT INTO Livro_Autor VALUES (?, ?)', [$codigo, $autor]);
        }

        foreach ($assuntos as $assunto) {
            $this->banco->executar('INSERT INTO Livro_Assunto VALUES (?, ?)', [$codigo, $assunto]);
        }
    }
}
