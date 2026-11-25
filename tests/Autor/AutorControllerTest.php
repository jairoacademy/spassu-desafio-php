<?php
declare(strict_types=1);

namespace Tests\Autor;

use App\Nucleo\Banco;
use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class AutorControllerTest extends TestCase
{
    private Banco $banco;
    private Sessao $sessao;
    private Http $http;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->banco = BancoDeTeste::recriar();
        $this->sessao = new Sessao();
        $view = new View($this->sessao, __DIR__ . '/../../views');
        $rotas = require __DIR__ . '/../../src/rotas.php';

        $this->http = new Http(fn(): array => $rotas($this->banco, $view, $this->sessao), $this->sessao, $view);
    }

    public function testCriarERedirecionar(): void
    {
        $resposta = $this->enviar('/autores', ['nome' => 'Fulano']);

        self::assertSame(303, $resposta->status);
        self::assertStringContainsString('Fulano', $this->visitar('/autores')->corpo);
    }

    public function testNomeVazioRetorna422(): void
    {
        $resposta = $this->enviar('/autores', ['nome' => '']);

        self::assertSame(422, $resposta->status);
        self::assertStringContainsString('Informe o nome.', $resposta->corpo);
    }

    public function testAlterarERedirecionar(): void
    {
        $this->enviar('/autores', ['nome' => 'Fulano']);

        self::assertSame(303, $this->enviar('/autores/1', ['nome' => 'Ciclano'])->status);
        self::assertStringContainsString('Ciclano', $this->visitar('/autores')->corpo);
    }

    public function testEditarInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->visitar('/autores/99/editar')->status);
    }

    public function testExcluirVinculadoMostraAviso(): void
    {
        $this->enviar('/autores', ['nome' => 'Fulano']);
        $this->banco->executar("INSERT INTO Livro VALUES (1, 'Livro', 'Editora', 1, '2026', 0)");
        $this->banco->executar('INSERT INTO Livro_Autor VALUES (1, 1)');
        $this->sessao->consumirAviso();

        self::assertSame(303, $this->enviar('/autores/1/excluir')->status);
        self::assertStringContainsString('Autor vinculado', $this->visitar('/autores')->corpo);
    }

    public function testListarSegundaPagina(): void
    {
        for ($codigo = 1; $codigo <= 15; $codigo++) {
            $this->banco->executar('INSERT INTO Autor VALUES (?, ?)', [$codigo, "Fulano {$codigo}"]);
        }

        $corpo = $this->visitar('/autores', ['pagina' => '2'])->corpo;

        self::assertStringContainsString('Mostrando 11–15 de 15 autores', $corpo);
        self::assertStringContainsString('>Fulano 5<', $corpo);
        self::assertStringNotContainsString('>Fulano 6<', $corpo);
    }

    public function testBuscarSemResultado(): void
    {
        $this->enviar('/autores', ['nome' => 'Fulano']);

        $corpo = $this->visitar('/autores', ['q' => 'Beltrano'])->corpo;

        self::assertStringContainsString('Nenhum autor encontrado para "Beltrano".', $corpo);
    }

    private function visitar(string $caminho, array $query = []): Resposta
    {
        return $this->http->processar(new Requisicao('GET', $caminho, $query));
    }

    private function enviar(string $caminho, array $dados = []): Resposta
    {
        $corpo = [...$dados, '_token' => $this->sessao->token()];

        return $this->http->processar(new Requisicao('POST', $caminho, corpo: $corpo));
    }
}
