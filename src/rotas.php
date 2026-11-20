<?php
declare(strict_types=1);

use App\Assunto\AssuntoController;
use App\Assunto\AssuntoRepositorio;
use App\Assunto\AssuntoServico;
use App\Autor\AutorController;
use App\Autor\AutorRepositorio;
use App\Autor\AutorServico;
use App\Livro\LivroController;
use App\Livro\LivroRepositorio;
use App\Livro\LivroServico;
use App\Nucleo\Banco;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;
use App\Relatorio\RelatorioController;
use App\Relatorio\RelatorioRepositorio;
use App\Relatorio\RelatorioServico;

return static function (Banco $banco, View $view, Sessao $sessao): array {
    $autorRepositorio = new AutorRepositorio($banco);
    $assuntoRepositorio = new AssuntoRepositorio($banco);
    $livroServico = new LivroServico(new LivroRepositorio($banco), $autorRepositorio, $assuntoRepositorio);

    $autores = new AutorController(new AutorServico($autorRepositorio), $view, $sessao);
    $assuntos = new AssuntoController(new AssuntoServico($assuntoRepositorio), $view, $sessao);
    $livros = new LivroController($livroServico, $view, $sessao);
    $relatorioServico = new RelatorioServico(new RelatorioRepositorio($banco));
    $relatorio = new RelatorioController($relatorioServico, $autorRepositorio, $view);

    return [
        'GET /' => static fn(): Resposta => Resposta::redirecionar('/livros'),
        'GET /saude' => static function () use ($banco): Resposta {
            $banco->consultar('SELECT 1');

            return Resposta::json(['status' => 'ok']);
        },

        'GET /livros' => $livros->listar(...),
        'GET /livros/novo' => $livros->novo(...),
        'POST /livros' => $livros->criar(...),
        'GET /livros/{id}/editar' => $livros->editar(...),
        'POST /livros/{id}' => $livros->alterar(...),
        'POST /livros/{id}/excluir' => $livros->excluir(...),

        'GET /autores' => $autores->listar(...),
        'GET /autores/novo' => $autores->novo(...),
        'POST /autores' => $autores->criar(...),
        'GET /autores/{id}/editar' => $autores->editar(...),
        'POST /autores/{id}' => $autores->alterar(...),
        'POST /autores/{id}/excluir' => $autores->excluir(...),

        'GET /assuntos' => $assuntos->listar(...),
        'GET /assuntos/novo' => $assuntos->novo(...),
        'POST /assuntos' => $assuntos->criar(...),
        'GET /assuntos/{id}/editar' => $assuntos->editar(...),
        'POST /assuntos/{id}' => $assuntos->alterar(...),
        'POST /assuntos/{id}/excluir' => $assuntos->excluir(...),

        'GET /relatorio' => $relatorio->livrosPorAutor(...),
    ];
};
