<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Livros por autor</h1>
    <button class="btn btn-primary nao-imprimir" type="button" onclick="window.print()">Imprimir</button>
</div>

<form class="row g-2 mb-4 nao-imprimir" method="get" action="/relatorio">
    <div class="col-auto">
        <label class="visually-hidden" for="autor">Autor</label>
        <select class="form-select" id="autor" name="autor">
            <option value="0">Todos os autores</option>
            <?php foreach ($autores as $autor): ?>
                <option value="<?= $this->e($autor['CodAutor']) ?>"
                    <?= (int) $autor['CodAutor'] === $autorSelecionado ? 'selected' : '' ?>>
                    <?= $this->e($autor['Nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
    </div>
</form>

<?php if ($relatorio['autores'] === []): ?>
    <p class="text-body-secondary">Nenhum livro encontrado para o relatório.</p>
<?php else: ?>
    <?php foreach ($relatorio['autores'] as $autor): ?>
        <h2 class="h5"><?= $this->e($autor['nome']) ?></h2>
        <div class="card border-0 shadow-sm table-responsive mb-4">
            <table class="table align-middle mb-0 tabela-relatorio">
                <colgroup>
                    <col class="coluna-titulo">
                    <col class="coluna-editora">
                    <col class="coluna-numero">
                    <col class="coluna-numero">
                    <col class="coluna-assuntos">
                    <col class="coluna-valor">
                </colgroup>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Editora</th>
                        <th class="text-end">Edição</th>
                        <th class="text-end">Ano</th>
                        <th>Assuntos</th>
                        <th class="text-end">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($autor['livros'] as $livro): ?>
                        <tr>
                            <td><?= $this->e($livro['Titulo']) ?></td>
                            <td><?= $this->e($livro['Editora']) ?></td>
                            <td class="text-end"><?= $this->e($livro['Edicao']) ?></td>
                            <td class="text-end"><?= $this->e($livro['AnoPublicacao']) ?></td>
                            <td><?= $this->e($livro['Assuntos'] ?? '') ?></td>
                            <td class="text-end text-nowrap">
                                <?= $this->formatarReais($livro['Valor']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-semibold">
                        <td colspan="5">Livros: <?= $this->e($autor['quantidade']) ?></td>
                        <td class="text-end text-nowrap">
                            <?= $this->formatarReais($autor['subtotal']) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between fw-bold border-top pt-3">
        <span>Total de livros: <?= $this->e($relatorio['totalLivros']) ?></span>
        <span>Total geral: <?= $this->formatarReais($relatorio['totalGeral']) ?></span>
    </div>
<?php endif; ?>
