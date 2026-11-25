<?php
declare(strict_types=1);

namespace App\Livro;

use App\Assunto\AssuntoRepositorio;
use App\Autor\AutorRepositorio;
use App\Nucleo\ErroAplicacao;

final readonly class LivroServico
{
    private const int TAMANHO_TEXTO = 40;
    private const float VALOR_MAXIMO = 99_999_999.99;

    public function __construct(
        private LivroRepositorio $livros,
        private AutorRepositorio $autores,
        private AssuntoRepositorio $assuntos,
    ) {
    }

    public function listar(int $limite = 20, int $pagina = 1, string $termo = ''): array
    {
        return $this->livros->listarComNomes($limite, $pagina, $termo);
    }

    public function opcoes(): array
    {
        return [$this->autores->todos(), $this->assuntos->todos()];
    }

    public function contar(string $termo = ''): int
    {
        return $this->livros->contar($termo);
    }

    public function buscar(int $codigo): array
    {
        $livro = $this->livros->buscar($codigo) ?? throw ErroAplicacao::naoEncontrado('Livro');

        return [
            ...$livro,
            'Autores' => $this->livros->autores($codigo),
            'Assuntos' => $this->livros->assuntos($codigo),
        ];
    }

    public function criar(array $dados): array
    {
        [$livro, $autores, $assuntos] = $this->validar($dados);
        $criado = $this->livros->criarComVinculos($livro, $autores, $assuntos);

        return $this->buscar($criado['CodLivro']);
    }

    public function alterar(int $codigo, array $dados): array
    {
        $this->buscar($codigo);
        [$livro, $autores, $assuntos] = $this->validar($dados);
        $this->livros->alterarComVinculos($codigo, $livro, $autores, $assuntos);

        return $this->buscar($codigo);
    }

    public function excluir(int $codigo): void
    {
        $this->buscar($codigo);
        $this->livros->excluir($codigo);
    }

    private function validar(array $dados): array
    {
        $titulo = $this->texto($dados['titulo'] ?? '');
        $editora = $this->texto($dados['editora'] ?? '');
        $edicao = $this->texto($dados['edicao'] ?? '');
        $ano = $this->texto($dados['ano'] ?? '');
        $valor = $this->valor($dados['valor'] ?? '');
        $autores = $this->codigos($dados['autores'] ?? []);
        $assuntos = $this->codigos($dados['assuntos'] ?? []);

        $erros = array_filter([
            'titulo' => $this->erroTexto($titulo, 'o título'),
            'editora' => $this->erroTexto($editora, 'a editora'),
            'edicao' => preg_match('/^[1-9]\d{0,3}$/', $edicao) ? null : 'Informe uma edição entre 1 e 9999.',
            'ano' => $this->erroAno($ano),
            'valor' => $valor === null ? 'Informe um valor entre 0 e 99.999.999,99.' : null,
            'autores' => $this->erroCodigos($autores, $this->autores->buscar(...), 'autor'),
            'assuntos' => $this->erroCodigos($assuntos, $this->assuntos->buscar(...), 'assunto'),
        ]);

        if ($erros !== []) {
            throw ErroAplicacao::validacao($erros);
        }

        $livro = [
            'Titulo' => $titulo,
            'Editora' => $editora,
            'Edicao' => (int) $edicao,
            'AnoPublicacao' => $ano,
            'Valor' => $valor,
        ];

        return [$livro, $autores, $assuntos];
    }

    private function texto(mixed $valor): string
    {
        return is_string($valor) || is_int($valor) ? trim((string) $valor) : '';
    }

    private function valor(mixed $valor): ?string
    {
        $valor = $this->texto($valor);

        if (str_contains($valor, ',')) {
            $valor = str_replace(['.', ','], ['', '.'], $valor);
        }

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $valor) || (float) $valor > self::VALOR_MAXIMO) {
            return null;
        }

        return number_format((float) $valor, 2, '.', '');
    }

    private function codigos(mixed $codigos): array
    {
        $codigos = is_array($codigos) ? array_map($this->texto(...), $codigos) : [];

        return array_values(array_unique(array_filter($codigos, ctype_digit(...))));
    }

    private function erroTexto(string $texto, string $campo): ?string
    {
        return match (true) {
            $texto === '' => "Informe {$campo}.",
            mb_strlen($texto) > self::TAMANHO_TEXTO => 'Use no máximo 40 caracteres.',
            default => null,
        };
    }

    private function erroAno(string $ano): ?string
    {
        if (!preg_match('/^\d{4}$/', $ano) || (int) $ano > (int) date('Y')) {
            return 'Informe um ano com 4 dígitos, até o ano atual.';
        }

        return null;
    }

    private function erroCodigos(array $codigos, callable $buscar, string $recurso): ?string
    {
        if ($codigos === []) {
            return "Selecione pelo menos um {$recurso}.";
        }

        foreach ($codigos as $codigo) {
            if ($buscar($codigo) === null) {
                return ucfirst($recurso) . ' inexistente.';
            }
        }

        return null;
    }
}
