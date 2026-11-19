<?php
declare(strict_types=1);

use App\Nucleo\Http;
use App\Nucleo\Sessao;
use App\Nucleo\View;

require __DIR__ . '/../vendor/autoload.php';

$sessao = new Sessao();
$view = new View($sessao, __DIR__ . '/../views');
$rotas = require __DIR__ . '/../src/rotas.php';

new Http(
    static fn(): array => $rotas(require __DIR__ . '/../src/conexao.php', $view, $sessao),
    $sessao,
    $view,
)->atender();
