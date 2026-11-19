<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\Banco;
use App\Nucleo\Repositorio;
use PHPUnit\Framework\TestCase;

final class RepositorioTest extends TestCase
{
    private Repositorio $registros;

    protected function setUp(): void
    {
        $banco = new Banco(
            (string) getenv('DB_DSN'),
            (string) getenv('DB_USUARIO'),
            (string) getenv('DB_SENHA'),
        );
        $banco->executar(
            'CREATE TEMPORARY TABLE registro (
                codigo INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(40) NOT NULL
            ) ENGINE=InnoDB',
        );

        $this->registros = new readonly class($banco) extends Repositorio {
            protected const string TABELA = 'registro';
            protected const string PK = 'codigo';
        };
    }

    public function testCriarEBuscar(): void
    {
        $registro = $this->registros->criar(['nome' => 'Fulano']);

        self::assertSame(['codigo' => 1, 'nome' => 'Fulano'], $registro);
        self::assertSame($registro, $this->registros->buscar(1));
    }

    public function testBuscarInexistenteRetornaNull(): void
    {
        self::assertNull($this->registros->buscar(999));
    }

    public function testCriarComIdInformado(): void
    {
        $registro = $this->registros->criar(['codigo' => 50, 'nome' => 'Fulano']);

        self::assertSame(50, $registro['codigo']);
    }

    public function testAlterar(): void
    {
        $this->registros->criar(['nome' => 'Fulano']);

        $registro = $this->registros->alterar(1, ['nome' => 'Ciclano']);

        self::assertSame(['codigo' => 1, 'nome' => 'Ciclano'], $registro);
    }

    public function testExcluir(): void
    {
        $this->registros->criar(['nome' => 'Fulano']);

        $this->registros->excluir(1);

        self::assertNull($this->registros->buscar(1));
    }

    public function testListarComPaginacao(): void
    {
        foreach (['Fulano', 'Ciclano', 'Beltrano'] as $nome) {
            $this->registros->criar(['nome' => $nome]);
        }

        self::assertSame(['Beltrano', 'Ciclano'], $this->nomesDaPagina(1));
        self::assertSame(['Fulano'], $this->nomesDaPagina(2));
        self::assertSame(3, $this->registros->contar());
    }

    private function nomesDaPagina(int $pagina): array
    {
        return array_column($this->registros->listar(2, $pagina), 'nome');
    }
}
