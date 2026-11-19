<?php
declare(strict_types=1);

namespace Tests;

use App\Nucleo\ErroAplicacao;
use App\Nucleo\Http;
use App\Nucleo\Requisicao;
use App\Nucleo\Resposta;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class HttpTest extends TestCase
{
    private Http $http;

    protected function setUp(): void
    {
        $this->http = new Http(static fn(): array => [
            'GET /registros' => static fn(Requisicao $requisicao): Resposta => Resposta::json(['pagina' => $requisicao->pagina()]),
            'GET /registros/{id}' => static fn(Requisicao $requisicao, string $id): Resposta => Resposta::json(['id' => $id]),
            'POST /registros' => static fn(Requisicao $requisicao): Resposta => Resposta::json(['nome' => $requisicao->texto('nome')], 201),
            'GET /invalido' => static fn(): never => throw ErroAplicacao::validacao(['nome' => 'Nome é obrigatório.']),
            'GET /quebrado' => static fn(): never => throw new RuntimeException('detalhe interno'),
        ]);
    }

    public function testRotaComId(): void
    {
        $resposta = $this->enviar('GET', '/registros/7');

        self::assertSame(200, $resposta->status);
        self::assertSame('{"id":"7"}', $resposta->corpo);
        self::assertSame('application/json; charset=utf-8', $resposta->cabecalhos['Content-Type']);
    }

    public function testRotaInexistenteRetorna404(): void
    {
        self::assertSame(404, $this->enviar('GET', '/nada')->status);
        self::assertSame(404, $this->enviar('DELETE', '/registros/7')->status);
    }

    public function testIdNaoNumericoRetorna404(): void
    {
        self::assertSame(404, $this->enviar('GET', '/registros/7abc')->status);
    }

    public function testTextoFazTrimEIgnoraArray(): void
    {
        self::assertSame('{"nome":"Fulano"}', $this->enviar('POST', '/registros', ['nome' => '  Fulano '])->corpo);
        self::assertSame('{"nome":""}', $this->enviar('POST', '/registros', ['nome' => ['Fulano']])->corpo);
    }

    public function testLimitesDaPagina(): void
    {
        $paginas = ['abc' => 1, '-3' => 1, '2' => 2, '99999999999999999999' => 1_000_000];

        foreach ($paginas as $recebida => $esperada) {
            $requisicao = new Requisicao('GET', '/registros', ['pagina' => (string) $recebida]);

            self::assertSame(json_encode(['pagina' => $esperada]), $this->http->processar($requisicao)->corpo);
        }
    }

    public function testErroDeValidacaoRetorna422(): void
    {
        $resposta = $this->enviar('GET', '/invalido');

        self::assertSame(422, $resposta->status);
        self::assertSame('{"mensagem":"Dados inválidos.","erros":{"nome":"Nome é obrigatório."}}', $resposta->corpo);
    }

    public function testErroInesperadoRetorna500(): void
    {
        $resposta = $this->enviar('GET', '/quebrado');

        self::assertSame(500, $resposta->status);
        self::assertStringNotContainsString('detalhe interno', $resposta->corpo);
    }

    private function enviar(string $metodo, string $caminho, array $corpo = []): Resposta
    {
        return $this->http->processar(new Requisicao($metodo, $caminho, corpo: $corpo));
    }
}
