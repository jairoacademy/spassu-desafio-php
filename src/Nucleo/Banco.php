<?php
declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use PDOException;
use PDOStatement;
use Throwable;

final readonly class Banco
{
    private const string VIOLACAO_DE_INTEGRIDADE = '23000';

    private PDO $pdo;

    public function __construct(string $dsn, string $usuario, string $senha)
    {
        try {
            $this->pdo = new PDO($dsn, $usuario, $senha, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $erro) {
            error_log($erro->getMessage());

            throw new ErroAplicacao('Banco de dados indisponível.', 503, causa: $erro);
        }
    }

    public function consultar(string $sql, array $parametros = []): array
    {
        return $this->preparar($sql, $parametros)->fetchAll();
    }

    public function executar(string $sql, array $parametros = []): int
    {
        return $this->preparar($sql, $parametros)->rowCount();
    }

    public function executarScript(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    public function ultimoId(): string
    {
        return (string) $this->pdo->lastInsertId();
    }

    public function transacao(callable $operacao): mixed
    {
        $this->pdo->beginTransaction();

        try {
            $resultado = $operacao();
            $this->pdo->commit();

            return $resultado;
        } catch (Throwable $erro) {
            $this->pdo->rollBack();

            throw $erro;
        }
    }

    private function preparar(string $sql, array $parametros): PDOStatement
    {
        try {
            $comando = $this->pdo->prepare($sql);
            $comando->execute($parametros);

            return $comando;
        } catch (PDOException $erro) {
            if ($erro->getCode() === self::VIOLACAO_DE_INTEGRIDADE) {
                throw new ErroAplicacao('O registro já existe ou está em uso.', 409, causa: $erro);
            }

            throw $erro;
        }
    }
}
