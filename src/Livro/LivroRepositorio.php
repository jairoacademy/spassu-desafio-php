<?php
declare(strict_types=1);

namespace App\Livro;

use App\Nucleo\Repositorio;

final readonly class LivroRepositorio extends Repositorio
{
    protected const string TABELA = 'Livro';
    protected const string PK = 'CodLivro';

    public function listarComNomes(int $limite = 20, int $pagina = 1, string $termo = ''): array
    {
        $deslocamento = ($pagina - 1) * $limite;
        [$filtro, $parametros] = $this->filtro($termo);

        return $this->banco->consultar(
            'SELECT Livro.*,'
            . ' (SELECT GROUP_CONCAT(Autor.Nome ORDER BY Autor.Nome SEPARATOR \', \')'
            . ' FROM Livro_Autor JOIN Autor ON Autor.CodAutor = Livro_Autor.Autor_CodAutor'
            . ' WHERE Livro_Autor.Livro_CodLivro = Livro.CodLivro) AS Autores,'
            . ' (SELECT GROUP_CONCAT(Assunto.Descricao ORDER BY Assunto.Descricao SEPARATOR \', \')'
            . ' FROM Livro_Assunto JOIN Assunto ON Assunto.CodAssunto = Livro_Assunto.Assunto_CodAssunto'
            . ' WHERE Livro_Assunto.Livro_CodLivro = Livro.CodLivro) AS Assuntos'
            . ' FROM Livro' . $filtro
            . ' ORDER BY Livro.CodLivro DESC'
            . " LIMIT {$limite} OFFSET {$deslocamento}",
            $parametros,
        );
    }

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

    public function isbnExistente(string $isbn, int $ignorarCodigo = 0): bool
    {
        $sql = 'SELECT 1 FROM Livro WHERE ISBN = ? AND CodLivro <> ? LIMIT 1';

        return $this->banco->consultar($sql, [$isbn, $ignorarCodigo]) !== [];
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

    protected function filtro(string $termo): array
    {
        if ($termo === '') {
            return ['', []];
        }

        $padrao = $this->padrao($termo);
        $isbn = $this->padrao(str_replace(['-', ' '], '', $termo));

        return [
            ' WHERE Livro.Titulo LIKE ? OR Livro.ISBN LIKE ? OR Livro.Editora LIKE ?'
            . ' OR EXISTS (SELECT 1 FROM Livro_Autor'
            . ' JOIN Autor ON Autor.CodAutor = Livro_Autor.Autor_CodAutor'
            . ' WHERE Livro_Autor.Livro_CodLivro = Livro.CodLivro AND Autor.Nome LIKE ?)',
            [$padrao, $isbn, $padrao, $padrao],
        ];
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
