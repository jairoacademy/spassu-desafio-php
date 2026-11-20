<?php
$aplicacao = 'Cadastro de Livros';
$menu = [
    '/livros' => 'Livros',
    '/autores' => 'Autores',
    '/assuntos' => 'Assuntos',
    '/relatorio' => 'Relatório',
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->e($titulo) ?> - <?= $this->e($aplicacao) ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/app.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-md menu-principal" data-bs-theme="dark">
        <div class="container">
            <a class="navbar-brand" href="/"><?= $this->e($aplicacao) ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"
                    aria-controls="menu" aria-expanded="false" aria-label="Abrir menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($menu as $caminho => $rotulo): ?>
                        <?php $ativo = str_starts_with($_SERVER['REQUEST_URI'] ?? '', $caminho); ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $ativo ? ' active' : '' ?>" href="<?= $this->e($caminho) ?>"
                                <?= $ativo ? 'aria-current="page"' : '' ?>><?= $this->e($rotulo) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container py-4 flex-grow-1">
        <?php if ($aviso !== null): ?>
            <div class="alert alert-<?= $this->e($aviso['tipo']) ?> alert-dismissible fade show" role="alert">
                <?= $this->e($aviso['mensagem']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        <?php endif; ?>

        <?= $conteudo ?>
    </main>

    <footer class="border-top py-3 small text-body-secondary">
        <div class="container"><?= $this->e($aplicacao) ?></div>
    </footer>

    <script src="/assets/vendor/bootstrap.bundle.min.js"></script>
    <script src="/assets/app.js"></script>
</body>

</html>
