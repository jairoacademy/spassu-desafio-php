<?php
declare(strict_types=1);

namespace App\Relatorio;

final readonly class RelatorioServico
{
    public function __construct(private RelatorioRepositorio $relatorio)
    {
    }

    public function agruparPorAutor(?int $codigoAutor = null): array
    {
        $autores = [];
        $valores = [];

        foreach ($this->relatorio->livrosPorAutor($codigoAutor) as $livro) {
            $codigo = $livro['CodAutor'];
            $centavos = $this->centavos($livro['Valor']);

            $autores[$codigo] ??= [
                'codigo' => $codigo,
                'nome' => $livro['Autor'],
                'livros' => [],
                'quantidade' => 0,
                'subtotal' => 0,
            ];
            $autores[$codigo]['livros'][] = $livro;
            $autores[$codigo]['quantidade']++;
            $autores[$codigo]['subtotal'] += $centavos;
            $valores[$livro['CodLivro']] = $centavos;
        }

        $autores = array_map(
            fn(array $autor): array => [...$autor, 'subtotal' => $this->reais($autor['subtotal'])],
            array_values($autores),
        );

        return [
            'autores' => $autores,
            'totalLivros' => count($valores),
            'totalGeral' => $this->reais(array_sum($valores)),
        ];
    }

    private function centavos(string $valor): int
    {
        return (int) round((float) $valor * 100);
    }

    private function reais(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}
