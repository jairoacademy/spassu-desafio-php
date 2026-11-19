<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\ErroAplicacao;
use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpTest extends TestCase
{
    private Http $http;
    private Sessao $sessao;

    protected function setUp(): void
    {
        $_SESSION = [];
        $this->sessao = new Sessao();
        $view = new View($this->sessao, __DIR__ . '/../views');
        $sessao = $this->sessao;

        $this->http = new Http(static fn(): array => [
            'GET /pagina' => static fn(): Resposta => $view->pagina('erro', [
                'titulo' => 'Teste',
                'status' => 200,
                'mensagem' => '<b>Olá</b>',
            ]),
            'GET /dados/{id}' => static fn(Requisicao $requisicao, string $id): Resposta => Resposta::json([
                'id' => $id,
            ]),
            'POST /dados' => static function () use ($sessao): Resposta {
                $sessao->avisar('Não foi possível salvar.', 'danger');

                return Resposta::redirecionar('/pagina');
            },
            'GET /invalido' => static fn(): never => throw ErroAplicacao::validacao(['nome' => 'Nome é obrigatório.']),
            'GET /quebrado' => static fn(): never => throw new RuntimeException('detalhe interno'),
        ], $this->sessao, $view);
    }

    public function testPaginaEscapaHtml(): void
    {
        $resposta = $this->visitar('/pagina');

        self::assertSame(200, $resposta->status);
        self::assertSame('text/html; charset=utf-8', $resposta->cabecalhos['Content-Type']);
        self::assertStringContainsString('/assets/vendor/bootstrap.min.css', $resposta->corpo);
        self::assertStringContainsString('&lt;b&gt;Olá&lt;/b&gt;', $resposta->corpo);
    }

    public function testRotaJson(): void
    {
        self::assertSame('{"id":"7"}', $this->visitar('/dados/7')->corpo);
    }

    public function testRotaInexistenteRetorna404(): void
    {
        $resposta = $this->visitar('/nada');

        self::assertSame(404, $resposta->status);
        self::assertStringContainsString('Voltar ao início', $resposta->corpo);
        self::assertSame(404, $this->visitar('/dados/7abc')->status);
    }

    public function testPostSemTokenRetorna403(): void
    {
        $requisicao = new Requisicao('POST', '/dados', corpo: ['_token' => 'falso']);

        self::assertSame(403, $this->http->processar($requisicao)->status);
    }

    public function testRedirecionarComAviso(): void
    {
        $requisicao = new Requisicao('POST', '/dados', corpo: ['_token' => $this->sessao->token()]);
        $resposta = $this->http->processar($requisicao);

        self::assertSame(303, $resposta->status);
        self::assertSame('/pagina', $resposta->cabecalhos['Location']);
        self::assertStringContainsString('alert-danger', $this->visitar('/pagina')->corpo);
        self::assertStringNotContainsString('alert-danger', $this->visitar('/pagina')->corpo);
    }

    public function testErroDeValidacaoRetorna422(): void
    {
        $resposta = $this->visitar('/invalido');

        self::assertSame(422, $resposta->status);
        self::assertStringContainsString('Dados inválidos.', $resposta->corpo);
    }

    public function testErroInesperadoRetorna500(): void
    {
        $resposta = $this->visitar('/quebrado');

        self::assertSame(500, $resposta->status);
        self::assertStringNotContainsString('detalhe interno', $resposta->corpo);
    }

    public function testTextoFazTrimELimitaPagina(): void
    {
        $requisicao = new Requisicao(
            'GET',
            '/',
            ['pagina' => '99999999999999999999'],
            ['nome' => '  Fulano ', 'lista' => ['a', ['b']]],
        );

        self::assertSame('Fulano', $requisicao->texto('nome'));
        self::assertSame('', $requisicao->texto('lista'));
        self::assertSame(['a'], $requisicao->lista('lista'));
        self::assertSame([], $requisicao->lista('nome'));
        self::assertSame(1_000_000, $requisicao->pagina());
    }

    private function visitar(string $caminho): Resposta
    {
        return $this->http->processar(new Requisicao('GET', $caminho));
    }
}
