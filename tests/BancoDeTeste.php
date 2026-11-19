<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\Banco;

final class BancoDeTeste
{
    private const string ESQUEMA = __DIR__ . '/../database/02-esquema.sql';

    public static function recriar(): Banco
    {
        $banco = new Banco(
            (string) getenv('DB_DSN'),
            (string) getenv('DB_USUARIO'),
            (string) getenv('DB_SENHA'),
        );
        $tabelas = $banco->consultar(
            'SELECT table_name AS nome FROM information_schema.tables WHERE table_schema = DATABASE()',
        );

        $banco->executar('SET FOREIGN_KEY_CHECKS = 0');

        foreach (array_column($tabelas, 'nome') as $tabela) {
            $banco->executar("DROP TABLE `{$tabela}`");
        }

        $banco->executar('SET FOREIGN_KEY_CHECKS = 1');
        $banco->executarScript((string) file_get_contents(self::ESQUEMA));

        return $banco;
    }
}
