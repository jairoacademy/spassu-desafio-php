<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Assuntos</h1>
    <a class="btn btn-primary" href="/assuntos/novo">Novo assunto</a>
</div>

<?php if ($assuntos === []): ?>
    <p class="text-body-secondary">Nenhum assunto cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assuntos as $assunto): ?>
                    <tr>
                        <td><?= $this->e($assunto['CodAssunto']) ?></td>
                        <td><?= $this->e($assunto['Descricao']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary"
                               href="/assuntos/<?= $this->e($assunto['CodAssunto']) ?>/editar">Editar</a>
                            <form class="d-inline" method="post"
                                  action="/assuntos/<?= $this->e($assunto['CodAssunto']) ?>/excluir"
                                  data-confirmar="Excluir este assunto?">
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
                    <a class="page-link" href="/assuntos?pagina=<?= $numero ?>"><?= $numero ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
