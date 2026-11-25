<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Autores</h1>
    <a class="btn btn-primary" href="/autores/novo">Novo autor</a>
</div>

<?php if ($autores === []): ?>
    <p class="text-body-secondary">Nenhum autor cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
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
        <div class="card-footer rodape-paginacao">
            <?= $this->parcial('paginacao', $paginacao) ?>
        </div>
    </div>
<?php endif; ?>
