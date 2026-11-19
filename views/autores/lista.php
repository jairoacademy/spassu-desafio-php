<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Autores</h1>
    <a class="btn btn-primary" href="/autores/novo">Novo autor</a>
</div>

<?php if ($autores === []): ?>
    <p class="text-body-secondary">Nenhum autor cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nome</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($autores as $autor): ?>
                    <tr>
                        <td><?= $this->e($autor['CodAutor']) ?></td>
                        <td><?= $this->e($autor['Nome']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="/autores/<?= $this->e($autor['CodAutor']) ?>/editar">Editar</a>
                            <form class="d-inline" method="post"
                                  action="/autores/<?= $this->e($autor['CodAutor']) ?>/excluir"
                                  data-confirmar="Excluir este autor?">
                                <?= $this->campoToken() ?>
                                <button class="btn btn-sm btn-outline-danger" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php if ($totalPaginas > 1): ?>
    <nav class="mt-3" aria-label="Paginação">
        <ul class="pagination">
            <?php for ($numero = 1; $numero <= $totalPaginas; $numero++): ?>
                <li class="page-item<?= $numero === $pagina ? ' active' : '' ?>">
                    <a class="page-link" href="/autores?pagina=<?= $numero ?>"><?= $numero ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
