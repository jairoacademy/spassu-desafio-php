<?php
declare(strict_types=1);

namespace Tests\Relatorio;

use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class RelatorioControllerTest extends TestCase
{
    public function testFiltrarPorAutor(): void
    {
        $_SESSION = [];
        $banco = BancoDeTeste::recriar();
        $banco->executar("INSERT INTO Autor VALUES (1, 'Fulano'), (2, 'Ciclano')");
        $banco->executar("INSERT INTO Assunto VALUES (1, 'Romance')");
        $banco->executar(
            "INSERT INTO Livro VALUES (1, 'Primeiro Livro', 'Editora Teste', 1, '2026', 10.00),"
            . " (2, 'Segundo Livro', 'Editora Teste', 1, '2026', 20.00)",
        );
        $banco->executar('INSERT INTO Livro_Autor VALUES (1, 1), (2, 2)');
        $banco->executar('INSERT INTO Livro_Assunto VALUES (1, 1), (2, 1)');

        $sessao = new Sessao();
        $view = new View($sessao, __DIR__ . '/../../views');
        $rotas = require __DIR__ . '/../../src/rotas.php';
        $http = new Http(fn(): array => $rotas($banco, $view, $sessao), $sessao, $view);

        $corpo = $http->processar(new Requisicao('GET', '/relatorio', ['autor' => '1']))->corpo;

        self::assertStringContainsString('Primeiro Livro', $corpo);
        self::assertStringNotContainsString('Segundo Livro', $corpo);
    }
}
