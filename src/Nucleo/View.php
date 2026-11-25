<?php
declare(strict_types=1);

namespace App\Nucleo;

final readonly class View
{
    public function __construct(private Sessao $sessao, private string $diretorio)
    {
    }

    public function pagina(string $template, array $dados = [], int $status = 200): Resposta
    {
        $conteudo = $this->renderizar($template, $dados);
        $aviso = $this->sessao->consumirAviso();

        $html = $this->renderizar('layout', [
            ...$dados,
            'conteudo' => $conteudo,
            'aviso' => $aviso,
        ]);

        return Resposta::html($html, $status);
    }

    public function e(mixed $valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function formatarValor(mixed $valor): string
    {
        return number_format((float) $valor, 2, ',', '.');
    }

    public function formatarReais(mixed $valor): string
    {
        return $this->e('R$ ' . $this->formatarValor($valor));
    }

    public function campoToken(): string
    {
        return '<input type="hidden" name="_token" value="' . $this->e($this->sessao->token()) . '">';
    }

    private function renderizar(string $template, array $dados): string
    {
        extract($dados, EXTR_SKIP);
        ob_start();

        try {
            require "{$this->diretorio}/{$template}.php";

            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }
}
