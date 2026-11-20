<?php
declare(strict_types=1);

namespace Tests\Relatorio;

use App\Nucleo\Banco;
use App\Relatorio\RelatorioRepositorio;
use App\Relatorio\RelatorioServico;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class RelatorioServicoTest extends TestCase
{
    private Banco $banco;
    private RelatorioServico $servico;

    protected function setUp(): void
    {
        $this->banco = BancoDeTeste::recriar();
        $this->banco->executar("INSERT INTO Autor VALUES (1, 'Fulano'), (2, 'Ciclano')");
        $this->banco->executar("INSERT INTO Assunto VALUES (1, 'Romance'), (3, 'Drama')");

        $this->servico = new RelatorioServico(new RelatorioRepositorio($this->banco));
    }

    public function testLivroComDoisAutoresSomaUmaVez(): void
    {
        $this->criarLivro(1, 'Primeiro Livro', '39.90', [1, 2], [1, 3]);

        $relatorio = $this->servico->agruparPorAutor();

        self::assertSame(['Ciclano', 'Fulano'], array_column($relatorio['autores'], 'nome'));
        self::assertSame('Drama, Romance', $relatorio['autores'][0]['livros'][0]['Assuntos']);
        self::assertSame(1, $relatorio['totalLivros']);
        self::assertSame('39.90', $relatorio['totalGeral']);
    }

    public function testFiltrarPorAutor(): void
    {
        $this->criarLivro(1, 'Primeiro Livro', '10.00', [1], [1]);
        $this->criarLivro(2, 'Segundo Livro', '20.00', [2], [1]);

        $relatorio = $this->servico->agruparPorAutor(2);

        self::assertSame(['Ciclano'], array_column($relatorio['autores'], 'nome'));
        self::assertSame('20.00', $relatorio['totalGeral']);
    }

    private function criarLivro(int $codigo, string $titulo, string $valor, array $autores, array $assuntos): void
    {
        $this->banco->executar(
            "INSERT INTO Livro VALUES (?, ?, 'Editora Teste', 1, '2026', ?)",
            [$codigo, $titulo, $valor],
        );

        foreach ($autores as $autor) {
            $this->banco->executar('INSERT INTO Livro_Autor VALUES (?, ?)', [$codigo, $autor]);
        }

        foreach ($assuntos as $assunto) {
            $this->banco->executar('INSERT INTO Livro_Assunto VALUES (?, ?)', [$codigo, $assunto]);
        }
    }
}
