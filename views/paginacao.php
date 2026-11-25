<?php
$totalPaginas = max(1, (int) ceil($total / $porPagina));
$inicio = ($pagina - 1) * $porPagina + 1;
$fim = min($pagina * $porPagina, $total);

$vizinhas = [1, $pagina - 1, $pagina, $pagina + 1, $totalPaginas];
$vizinhas = array_filter(
    array_unique($vizinhas),
    static fn(int $numero): bool => $numero >= 1 && $numero <= $totalPaginas,
);
sort($vizinhas);

$numeros = [];
$ultimo = 0;
foreach ($vizinhas as $numero) {
    if ($numero - $ultimo === 2) {
        $numeros[] = $numero - 1;
    } elseif ($numero - $ultimo > 2) {
        $numeros[] = null;
    }
    $numeros[] = $numero;
    $ultimo = $numero;
}

$anterior = max(1, min($pagina - 1, $totalPaginas));
$link = static fn(int $numero): string => "{$rota}?pagina={$numero}";
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span class="small text-body-secondary">
        <?php if ($inicio <= $total): ?>
            Mostrando <?= $inicio ?>–<?= $fim ?> de <?= $total ?> <?= $this->e($rotulo) ?>
        <?php else: ?>
            Página <?= $pagina ?> de <?= $totalPaginas ?>
        <?php endif; ?>
    </span>

    <?php if ($totalPaginas > 1): ?>
        <nav aria-label="Paginação">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item<?= $pagina <= 1 ? ' disabled' : '' ?>">
                    <a class="page-link" href="<?= $this->e($link($anterior)) ?>">Anterior</a>
                </li>
                <?php foreach ($numeros as $numero): ?>
                    <?php if ($numero === null): ?>
                        <li class="page-item disabled"><span class="page-link">…</span></li>
                    <?php else: ?>
                        <li class="page-item<?= $numero === $pagina ? ' active' : '' ?>">
                            <a class="page-link" href="<?= $this->e($link($numero)) ?>"
                                <?= $numero === $pagina ? 'aria-current="page"' : '' ?>><?= $numero ?></a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                <li class="page-item<?= $pagina >= $totalPaginas ? ' disabled' : '' ?>">
                    <a class="page-link" href="<?= $this->e($link($pagina + 1)) ?>">Próxima</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>
