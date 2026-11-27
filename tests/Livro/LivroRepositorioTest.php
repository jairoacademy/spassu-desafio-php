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

    public function testBuscarPorTituloOuEditora(): void
    {
        $this->livros->criarComVinculos($this->dados(), [1], [1]);
        $segundo = $this->dados([
            'Titulo' => 'Segundo Livro',
            'ISBN' => '9788535902772',
            'Editora' => 'Outra Editora',
        ]);
        $this->livros->criarComVinculos($segundo, [1], [1]);

        self::assertSame(['Segundo Livro'], $this->titulos('segundo'));
        self::assertSame(['Segundo Livro'], $this->titulos('outra'));
    }

    public function testBuscarPorIsbn(): void
    {
        $this->livros->criarComVinculos($this->dados(), [1], [1]);

        self::assertSame(['Primeiro Livro'], $this->titulos('978-85-359'));
    }

    public function testBuscarPorAutorSemDuplicar(): void
    {
        $this->livros->criarComVinculos($this->dados(), [1, 2], [1]);

        self::assertSame(['Primeiro Livro'], $this->titulos('lano'));
        self::assertSame(1, $this->livros->contar('lano'));
    }

    public function testTermoSemResultado(): void
    {
        $this->livros->criarComVinculos($this->dados(), [1], [1]);

        self::assertSame([], $this->titulos('Beltrano'));
        self::assertSame(0, $this->livros->contar('%'));
    }

    private function titulos(string $termo): array
    {
        return array_column($this->livros->listarComNomes(10, 1, $termo), 'Titulo');
    }

    private function dados(array $alteracoes = []): array
    {
        return [
            'Titulo' => 'Primeiro Livro',
            'ISBN' => '9788535902771',
            'Editora' => 'Editora Teste',
            'Edicao' => 1,
            'AnoPublicacao' => '2026',
            'Valor' => '39.90',
            ...$alteracoes,
        ];
    }
}
