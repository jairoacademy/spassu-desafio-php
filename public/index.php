<?php
declare(strict_types=1);

use App\Nucleo\Http;

require __DIR__ . '/../vendor/autoload.php';

$rotas = require __DIR__ . '/../src/rotas.php';

new Http(static fn(): array => $rotas(require __DIR__ . '/../src/conexao.php'))->atender();
