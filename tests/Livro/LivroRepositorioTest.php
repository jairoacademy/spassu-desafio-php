<?php
declare(strict_types=1);

namespace Tests\Livro;

use App\Livro\LivroRepositorio;
use App\Nucleo\Banco;
use App\Nucleo\ErroAplicacao;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class LivroRepositorioTest extends TestCase
{
    private Banco $banco;
    private LivroRepositorio $livros;

    protected function setUp(): void
    {
        $this->banco = BancoDeTeste::recriar();
        $this->livros = new LivroRepositorio($this->banco);
        $this->banco->executar("INSERT INTO Autor VALUES (1, 'Fulano'), (2, 'Ciclano')");
        $this->banco->executar("INSERT INTO Assunto VALUES (1, 'Romance'), (2, 'Poesia')");
    }

    public function testCriarComVinculos(): void
    {
        $livro = $this->livros->criarComVinculos($this->dados(), [1, 2], [1]);

        self::assertSame([1, 2], $this->livros->autores($livro['CodLivro']));
        self::assertSame([1], $this->livros->assuntos($livro['CodLivro']));
    }

    public function testAlterarTrocaVinculos(): void
    {
        $this->livros->criarComVinculos($this->dados(), [1], [1]);
        $this->livros->alterarComVinculos(1, $this->dados(), [2], [2]);

        self::assertSame([2], $this->livros->autores(1));
        self::assertSame([2], $this->livros->assuntos(1));
    }

    public function testRollbackComAutorInexistente(): void
    {
        try {
            $this->livros->criarComVinculos($this->dados(), [99], [1]);
            self::fail('Era esperado um ErroAplicacao.');
        } catch (ErroAplicacao $erro) {
            self::assertSame(409, $erro->status);
        }

        self::assertSame(0, $this->livros->contar());
    }

    private function dados(): array
    {
        return [
            'Titulo' => 'Primeiro Livro',
            'Editora' => 'Editora Teste',
            'Edicao' => 1,
            'AnoPublicacao' => '2026',
            'Valor' => '39.90',
        ];
    }
}
