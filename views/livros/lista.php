<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Livros</h1>
    <a class="btn btn-primary" href="/livros/novo">Novo livro</a>
</div>

<?php if ($livros === []): ?>
    <p class="text-body-secondary">Nenhum livro cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Título</th>
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
                        <td><?= $this->e($livro['Autores']) ?></td>
                        <td><?= $this->e($livro['Assuntos']) ?></td>
                        <td><?= $this->e($livro['Editora']) ?></td>
                        <td class="text-end"><?= $this->e($livro['Edicao']) ?></td>
                        <td class="text-end"><?= $this->e($livro['AnoPublicacao']) ?></td>
                        <td class="text-end text-nowrap">
                            R$ <?= $this->e(number_format((float) $livro['Valor'], 2, ',', '.')) ?>
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
<?php endif; ?>

<?php if ($totalPaginas > 1): ?>
    <nav class="mt-3" aria-label="Paginação">
        <ul class="pagination">
            <?php for ($numero = 1; $numero <= $totalPaginas; $numero++): ?>
                <li class="page-item<?= $numero === $pagina ? ' active' : '' ?>">
                    <a class="page-link" href="/livros?pagina=<?= $numero ?>"><?= $numero ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
