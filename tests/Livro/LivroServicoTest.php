<?php
declare(strict_types=1);

namespace Tests\Livro;

use App\Assunto\AssuntoRepositorio;
use App\Autor\AutorRepositorio;
use App\Livro\LivroRepositorio;
use App\Livro\LivroServico;
use App\Nucleo\ErroAplicacao;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class LivroServicoTest extends TestCase
{
    private LivroServico $servico;

    protected function setUp(): void
    {
        $banco = BancoDeTeste::recriar();
        $banco->executar("INSERT INTO Autor VALUES (1, 'Fulano')");
        $banco->executar("INSERT INTO Assunto VALUES (1, 'Romance')");

        $this->servico = new LivroServico(
            new LivroRepositorio($banco),
            new AutorRepositorio($banco),
            new AssuntoRepositorio($banco),
        );
    }

    public function testCriarComValorEmReais(): void
    {
        $livro = $this->servico->criar($this->dados(['valor' => '1.234,50']));

        self::assertSame('1234.50', $livro['Valor']);
        self::assertSame([1], $livro['Autores']);
    }

    public function testCamposVaziosRetorna422(): void
    {
        $erro = $this->capturarErro(fn() => $this->servico->criar([]));

        self::assertSame(422, $erro->status);
        self::assertCount(7, $erro->erros);
    }

    public function testAutorInexistenteRetorna422(): void
    {
        $erro = $this->capturarErro(fn() => $this->servico->criar($this->dados(['autores' => ['99']])));

        self::assertSame('Autor inexistente.', $erro->erros['autores']);
    }

    public function testBuscarInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->capturarErro(fn() => $this->servico->buscar(99))->status);
    }

    public function testExcluirLivro(): void
    {
        $this->servico->criar($this->dados());
        $this->servico->excluir(1);

        self::assertSame(404, $this->capturarErro(fn() => $this->servico->buscar(1))->status);
    }

    private function dados(array $alteracoes = []): array
    {
        return [
            'titulo' => 'Primeiro Livro',
            'editora' => 'Editora Teste',
            'edicao' => '1',
            'ano' => '2026',
            'valor' => '39,90',
            'autores' => ['1'],
            'assuntos' => ['1'],
            ...$alteracoes,
        ];
    }

    private function capturarErro(callable $operacao): ErroAplicacao
    {
        try {
            $operacao();
        } catch (ErroAplicacao $erro) {
            return $erro;
        }

        self::fail('Era esperado um ErroAplicacao.');
    }
}
