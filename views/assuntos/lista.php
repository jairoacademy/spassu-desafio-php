<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Assuntos</h1>
    <a class="btn btn-primary" href="/assuntos/novo">Novo assunto</a>
</div>

<?= $this->parcial('busca', ['rota' => '/assuntos', 'termo' => $termo, 'placeholder' => 'Buscar por descrição...']) ?>

<?php if ($assuntos === [] && $termo !== ''): ?>
    <p class="text-body-secondary">Nenhum assunto encontrado para "<?= $this->e($termo) ?>".</p>
<?php elseif ($assuntos === []): ?>
    <p class="text-body-secondary">Nenhum assunto cadastrado.</p>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
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
        <div class="card-footer rodape-paginacao">
            <?= $this->parcial('paginacao', $paginacao) ?>
        </div>
    </div>
<?php endif; ?>
