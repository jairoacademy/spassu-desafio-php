<?php
declare(strict_types=1);

namespace Tests\Autor;

use App\Autor\AutorRepositorio;
use App\Autor\AutorServico;
use App\Nucleo\Banco;
use App\Nucleo\ErroAplicacao;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class AutorServicoTest extends TestCase
{
    private Banco $banco;
    private AutorServico $servico;

    protected function setUp(): void
    {
        $this->banco = BancoDeTeste::recriar();
        $this->servico = new AutorServico(new AutorRepositorio($this->banco));
    }

    public function testCriarFazTrim(): void
    {
        self::assertSame('Fulano', $this->servico->criar('  Fulano ')['Nome']);
    }

    public function testNomeVazioRetorna422(): void
    {
        self::assertSame(422, $this->capturarErro(fn() => $this->servico->criar(''))->status);
    }

    public function testNomeDuplicadoRetorna422(): void
    {
        $this->servico->criar('Fulano');

        self::assertSame(422, $this->capturarErro(fn() => $this->servico->criar('Fulano'))->status);
    }

    public function testBuscarInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->capturarErro(fn() => $this->servico->buscar(99))->status);
    }

    public function testExcluirVinculadoRetorna409(): void
    {
        $this->servico->criar('Fulano');
        $this->banco->executar("INSERT INTO Livro VALUES (1, 'Livro', '9788535902778', 'Editora', 1, '2026', 0)");
        $this->banco->executar('INSERT INTO Livro_Autor VALUES (1, 1)');

        self::assertSame(409, $this->capturarErro(fn() => $this->servico->excluir(1))->status);
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
