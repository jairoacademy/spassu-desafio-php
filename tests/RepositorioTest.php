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
        $banco = new Banco((string) getenv('DB_DSN'), (string) getenv('DB_USUARIO'), (string) getenv('DB_SENHA'));
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
        self::assertSame(50, $this->registros->criar(['codigo' => 50, 'nome' => 'Fulano'])['codigo']);
    }

    public function testAlterar(): void
    {
        $this->registros->criar(['nome' => 'Fulano']);

        self::assertSame(['codigo' => 1, 'nome' => 'Ciclano'], $this->registros->alterar(1, ['nome' => 'Ciclano']));
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

        self::assertSame(['Beltrano', 'Ciclano'], array_column($this->registros->listar(2, 1), 'nome'));
        self::assertSame(['Fulano'], array_column($this->registros->listar(2, 2), 'nome'));
        self::assertSame(3, $this->registros->contar());
    }
}
