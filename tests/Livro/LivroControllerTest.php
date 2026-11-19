<?php
declare(strict_types=1);

namespace Tests\Livro;

use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class LivroControllerTest extends TestCase
{
    private Sessao $sessao;
    private Http $http;

    protected function setUp(): void
    {
        $_SESSION = [];
        $banco = BancoDeTeste::recriar();
        $banco->executar("INSERT INTO Autor VALUES (1, 'Fulano'), (2, 'Ciclano')");
        $banco->executar("INSERT INTO Assunto VALUES (1, 'Romance'), (2, 'Poesia')");
        $this->sessao = new Sessao();
        $view = new View($this->sessao, __DIR__ . '/../../views');
        $rotas = require __DIR__ . '/../../src/rotas.php';

        $this->http = new Http(fn(): array => $rotas($banco, $view, $this->sessao), $this->sessao, $view);
    }

    public function testCriarERedirecionar(): void
    {
        self::assertSame(303, $this->enviar('/livros', $this->dados())->status);

        $lista = $this->visitar('/livros')->corpo;

        self::assertStringContainsString('Primeiro Livro', $lista);
        self::assertStringContainsString('Ciclano, Fulano', $lista);
        self::assertStringContainsString('R$ 1.234,50', $lista);
    }

    public function testCamposVaziosRetorna422(): void
    {
        $resposta = $this->enviar('/livros', ['titulo' => 'Primeiro Livro']);

        self::assertSame(422, $resposta->status);
        self::assertStringContainsString('value="Primeiro Livro"', $resposta->corpo);
        self::assertStringContainsString('Selecione pelo menos um autor.', $resposta->corpo);
    }

    public function testEditarMostraValorEVinculos(): void
    {
        $this->enviar('/livros', $this->dados());

        $formulario = $this->visitar('/livros/1/editar')->corpo;

        self::assertStringContainsString('value="1.234,50"', $formulario);
        self::assertMatchesRegularExpression('/id="autores-2"[^>]*checked/', $formulario);
    }

    public function testEditarInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->visitar('/livros/99/editar')->status);
    }

    public function testExcluirERedirecionar(): void
    {
        $this->enviar('/livros', $this->dados());

        self::assertSame(303, $this->enviar('/livros/1/excluir')->status);
        self::assertStringContainsString('Nenhum livro cadastrado.', $this->visitar('/livros')->corpo);
    }

    private function dados(): array
    {
        return [
            'titulo' => 'Primeiro Livro',
            'editora' => 'Editora Teste',
            'edicao' => '1',
            'ano' => '2026',
            'valor' => '1.234,50',
            'autores' => ['1', '2'],
            'assuntos' => ['1'],
        ];
    }

    private function visitar(string $caminho): Resposta
    {
        return $this->http->processar(new Requisicao('GET', $caminho));
    }

    private function enviar(string $caminho, array $dados = []): Resposta
    {
        $corpo = [...$dados, '_token' => $this->sessao->token()];

        return $this->http->processar(new Requisicao('POST', $caminho, corpo: $corpo));
    }
}
