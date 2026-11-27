<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\Banco;
use App\Nucleo\ErroAplicacao;
use PHPUnit\Framework\TestCase;

final class EsquemaTest extends TestCase
{
    private Banco $banco;

    protected function setUp(): void
    {
        $this->banco = BancoDeTeste::recriar();
    }

    public function testAutorInexistenteRetorna409(): void
    {
        $livro = $this->criarLivro();

        $this->assertConflito(fn() => $this->vincular($livro, 999));
        self::assertSame(0, $this->contar('Livro_Autor'));
    }

    public function testExcluirLivroApagaVinculos(): void
    {
        $livro = $this->criarLivro();
        $this->vincular($livro, $this->criarAutor('Fulano'));

        $this->banco->executar('DELETE FROM Livro WHERE CodLivro = ?', [$livro]);

        self::assertSame(0, $this->contar('Livro_Autor'));
    }

    public function testExcluirAutorVinculadoRetorna409(): void
    {
        $autor = $this->criarAutor('Fulano');
        $this->vincular($this->criarLivro(), $autor);

        $this->assertConflito(
            fn() => $this->banco->executar('DELETE FROM Autor WHERE CodAutor = ?', [$autor]),
        );
        self::assertSame(1, $this->contar('Autor'));
    }

    public function testVinculoDuplicadoRetorna409(): void
    {
        $livro = $this->criarLivro();
        $autor = $this->criarAutor('Fulano');
        $this->vincular($livro, $autor);

        $this->assertConflito(fn() => $this->vincular($livro, $autor));
        self::assertSame(1, $this->contar('Livro_Autor'));
    }

    private function criarLivro(): int
    {
        $this->banco->executar(
            'INSERT INTO Livro (Titulo, ISBN, Editora, Edicao, AnoPublicacao) VALUES (?, ?, ?, ?, ?)',
            ['Livro do Fulano', '9788535902778', 'Editora', 1, '2026'],
        );

        return (int) $this->banco->ultimoId();
    }

    private function criarAutor(string $nome): int
    {
        $this->banco->executar('INSERT INTO Autor (Nome) VALUES (?)', [$nome]);

        return (int) $this->banco->ultimoId();
    }

    private function vincular(int $livro, int $autor): void
    {
        $this->banco->executar(
            'INSERT INTO Livro_Autor (Livro_CodLivro, Autor_CodAutor) VALUES (?, ?)',
            [$livro, $autor],
        );
    }

    private function contar(string $tabela): int
    {
        return (int) $this->banco->consultar("SELECT COUNT(*) AS total FROM {$tabela}")[0]['total'];
    }

    private function assertConflito(callable $operacao): void
    {
        try {
            $operacao();
            self::fail('Era esperado um conflito.');
        } catch (ErroAplicacao $erro) {
            self::assertSame(409, $erro->status);
        }
    }
}
