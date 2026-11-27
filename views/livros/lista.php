<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Livros</h1>
        <p class="text-body-secondary mb-0">Catálogo completo com autores, assuntos e valores.</p>
    </div>
    <a class="btn btn-primary" href="/livros/novo">Novo livro</a>
</div>

<?= $this->parcial('busca', [
    'rota' => '/livros',
    'termo' => $termo,
    'placeholder' => 'Buscar por título, ISBN, editora ou autor...',
]) ?>

<?php if ($livros === [] && $termo !== ''): ?>
    <p class="text-body-secondary">Nenhum livro encontrado para "<?= $this->e($termo) ?>".</p>
<?php elseif ($livros === []): ?>
    <p class="text-body-secondary">Nenhum livro cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>ISBN</th>
                        <th>Autores</th>
                        <th>Assuntos</th>
                        <th>Editora</th>
                        <th class="text-end">Edição</th>
                        <th class="text-end">Ano</th>
                        <th class="text-end">Valor</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livros as $livro): ?>
                        <tr>
                            <td><?= $this->e($livro['Titulo']) ?></td>
                            <td class="text-nowrap"><?= $this->e($livro['ISBN']) ?></td>
                            <td><?= $this->e($livro['Autores']) ?></td>
                            <td><?= $this->e($livro['Assuntos']) ?></td>
                            <td class="text-nowrap"><?= $this->e($livro['Editora']) ?></td>
                            <td class="text-end"><?= $this->e($livro['Edicao']) ?></td>
                            <td class="text-end"><?= $this->e($livro['AnoPublicacao']) ?></td>
                            <td class="text-end text-nowrap">
                                <?= $this->formatarReais($livro['Valor']) ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="/livros/<?= $this->e($livro['CodLivro']) ?>/editar">Editar</a>
                                <form class="d-inline" method="post"
                                      action="/livros/<?= $this->e($livro['CodLivro']) ?>/excluir"
                                      data-confirmar="Excluir este livro?">
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
