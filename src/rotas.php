<?php
declare(strict_types=1);

use App\Autor\AutorController;
use App\Autor\AutorRepositorio;
use App\Autor\AutorServico;
use App\Nucleo\Banco;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;

return static function (Banco $banco, View $view, Sessao $sessao): array {
    $autores = new AutorController(new AutorServico(new AutorRepositorio($banco)), $view, $sessao);

    return [
        'GET /saude' => static function () use ($banco): Resposta {
            $banco->consultar('SELECT 1');

            return Resposta::json(['status' => 'ok']);
        },
        'GET /autores' => $autores->listar(...),
        'GET /autores/novo' => $autores->novo(...),
        'POST /autores' => $autores->criar(...),
        'GET /autores/{id}/editar' => $autores->editar(...),
        'POST /autores/{id}' => $autores->alterar(...),
        'POST /autores/{id}/excluir' => $autores->excluir(...),
    ];
};
