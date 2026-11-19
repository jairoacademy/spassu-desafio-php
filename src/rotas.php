<?php
declare(strict_types=1);

use App\Nucleo\Banco;
use App\Nucleo\Resposta;
use App\Nucleo\Sessao;
use App\Nucleo\View;

return static function (Banco $banco, View $view, Sessao $sessao): array {
    return [
        'GET /saude' => static function () use ($banco): Resposta {
            $banco->consultar('SELECT 1');

            return Resposta::json(['status' => 'ok']);
        },
    ];
};
