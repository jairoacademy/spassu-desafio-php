<?php
declare(strict_types=1);

namespace Tests\Assunto;

use App\Nucleo\Banco;
use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class AssuntoControllerTest extends TestCase
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
        $resposta = $this->enviar('/assuntos', ['descricao' => 'Romance']);

        self::assertSame(303, $resposta->status);
        self::assertStringContainsString('Romance', $this->visitar('/assuntos')->corpo);
    }

    public function testDescricaoVaziaRetorna422(): void
    {
        $resposta = $this->enviar('/assuntos', ['descricao' => '']);

        self::assertSame(422, $resposta->status);
        self::assertStringContainsString('Informe a descrição.', $resposta->corpo);
    }

    public function testAlterarERedirecionar(): void
    {
        $this->enviar('/assuntos', ['descricao' => 'Romance']);

        self::assertSame(303, $this->enviar('/assuntos/1', ['descricao' => 'Poesia'])->status);
        self::assertStringContainsString('Poesia', $this->visitar('/assuntos')->corpo);
    }

    public function testEditarInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->visitar('/assuntos/99/editar')->status);
    }

    public function testExcluirVinculadoMostraAviso(): void
    {
        $this->enviar('/assuntos', ['descricao' => 'Romance']);
        $this->banco->executar("INSERT INTO Livro VALUES (1, 'Livro', '9788535902778', 'Editora', 1, '2026', 0)");
        $this->banco->executar('INSERT INTO Livro_Assunto VALUES (1, 1)');
        $this->sessao->consumirAviso();

        self::assertSame(303, $this->enviar('/assuntos/1/excluir')->status);
        self::assertStringContainsString('Assunto vinculado', $this->visitar('/assuntos')->corpo);
    }

    public function testBuscarPorDescricao(): void
    {
        $this->enviar('/assuntos', ['descricao' => 'Romance']);
        $this->enviar('/assuntos', ['descricao' => 'Poesia']);

        $corpo = $this->visitar('/assuntos', ['q' => 'poe'])->corpo;

        self::assertStringContainsString('>Poesia<', $corpo);
        self::assertStringNotContainsString('>Romance<', $corpo);
        self::assertStringContainsString('value="poe"', $corpo);
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
