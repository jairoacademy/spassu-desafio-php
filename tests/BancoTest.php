<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\Banco;
use App\Nucleo\ErroAplicacao;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class BancoTest extends TestCase
{
    private Banco $banco;

    protected function setUp(): void
    {
        $this->banco = new Banco((string) getenv('DB_DSN'), (string) getenv('DB_USUARIO'), (string) getenv('DB_SENHA'));
        $this->banco->executar(
            'CREATE TEMPORARY TABLE registro (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(40) NOT NULL UNIQUE
            ) ENGINE=InnoDB',
        );
    }

    public function testInserirEConsultar(): void
    {
        self::assertSame(1, $this->inserir('Alfa'));
        self::assertSame('1', $this->banco->ultimoId());
        self::assertSame(
            [['id' => 1, 'nome' => 'Alfa']],
            $this->banco->consultar('SELECT * FROM registro WHERE nome = ?', ['Alfa']),
        );
    }

    public function testCommitDaTransacao(): void
    {
        $resultado = $this->banco->transacao(fn() => $this->inserir('Alfa'));

        self::assertSame(1, $resultado);
        self::assertSame(1, $this->total());
    }

    public function testRollbackDaTransacao(): void
    {
        try {
            $this->banco->transacao(function (): void {
                $this->inserir('Alfa');

                throw new RuntimeException('falha');
            });
            self::fail('A exceção deveria ser relançada.');
        } catch (RuntimeException $erro) {
            self::assertSame('falha', $erro->getMessage());
        }

        self::assertSame(0, $this->total());
    }

    public function testRegistroDuplicadoRetorna409(): void
    {
        $this->inserir('Alfa');

        try {
            $this->inserir('Alfa');
            self::fail('Era esperado um conflito.');
        } catch (ErroAplicacao $erro) {
            self::assertSame(409, $erro->status);
            self::assertInstanceOf(PDOException::class, $erro->getPrevious());
        }
    }

    public function testSqlInvalidoLancaPdoException(): void
    {
        $this->expectException(PDOException::class);

        $this->banco->consultar('SELECT * FROM tabela_inexistente');
    }

    public function testConexaoInvalidaRetorna503(): void
    {
        try {
            new Banco('mysql:host=127.0.0.1;port=1;dbname=livros', 'app', 'app');
            self::fail('Era esperada uma falha de conexão.');
        } catch (ErroAplicacao $erro) {
            self::assertSame(503, $erro->status);
        }
    }

    private function inserir(string $nome): int
    {
        return $this->banco->executar('INSERT INTO registro (nome) VALUES (?)', [$nome]);
    }

    private function total(): int
    {
        return (int) $this->banco->consultar('SELECT COUNT(*) AS total FROM registro')[0]['total'];
    }
}
