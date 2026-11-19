<?php
declare(strict_types=1);

use App\Nucleo\Banco;

return new Banco((string) getenv('DB_DSN'), (string) getenv('DB_USUARIO'), (string) getenv('DB_SENHA'));
