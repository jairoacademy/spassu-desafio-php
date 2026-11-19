<?php
declare(strict_types=1);

namespace Tests\Assunto;

use App\Assunto\AssuntoRepositorio;
use App\Assunto\AssuntoServico;
use App\Nucleo\Banco;
use App\Nucleo\ErroAplicacao;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class AssuntoServicoTest extends TestCase
{
    private Banco $banco;
    private AssuntoServico $servico;

    protected function setUp(): void
    {
        $this->banco = BancoDeTeste::recriar();
        $this->servico = new AssuntoServico(new AssuntoRepositorio($this->banco));
    }

    public function testCriarFazTrim(): void
    {
        self::assertSame('Fulano', $this->servico->criar('  Fulano ')['Descricao']);
    }

    public function testDescricaoVaziaRetorna422(): void
    {
        self::assertSame(422, $this->capturarErro(fn() => $this->servico->criar(''))->status);
    }

    public function testDescricaoDuplicadaRetorna422(): void
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
        $this->banco->executar("INSERT INTO Livro VALUES (1, 'Livro', 'Editora', 1, '2026', 0)");
        $this->banco->executar('INSERT INTO Livro_Assunto VALUES (1, 1)');

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
