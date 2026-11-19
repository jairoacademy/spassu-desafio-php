<?php
$campos = [
    'titulo' => ['Título', 'col-md-8', 'maxlength="40"'],
    'editora' => ['Editora', 'col-md-4', 'maxlength="40"'],
    'edicao' => ['Edição', 'col-md-4', 'inputmode="numeric" maxlength="4"'],
    'ano' => ['Ano de publicação', 'col-md-4', 'inputmode="numeric" maxlength="4"'],
    'valor' => ['Valor (R$)', 'col-md-4', 'inputmode="decimal" placeholder="0,00"'],
];
$grupos = [
    'autores' => ['Autores', $autores, 'CodAutor', 'Nome', '/autores/novo'],
    'assuntos' => ['Assuntos', $assuntos, 'CodAssunto', 'Descricao', '/assuntos/novo'],
];
?>
<h1 class="h3 mb-3"><?= $this->e($titulo) ?></h1>

<form class="card border-0 shadow-sm" method="post" action="<?= $this->e($acao) ?>" novalidate>
    <div class="card-body row g-3">
        <?= $this->campoToken() ?>

        <?php foreach ($campos as $campo => [$rotulo, $coluna, $atributos]): ?>
            <div class="<?= $coluna ?>">
                <label class="form-label" for="<?= $campo ?>"><?= $rotulo ?></label>
                <input class="form-control<?= isset($erros[$campo]) ? ' is-invalid' : '' ?>"
                       id="<?= $campo ?>" name="<?= $campo ?>" value="<?= $this->e($livro[$campo]) ?>"
                       <?= $atributos ?> required>
                <?php if (isset($erros[$campo])): ?>
                    <div class="invalid-feedback"><?= $this->e($erros[$campo]) ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php foreach ($grupos as $campo => [$rotulo, $opcoes, $chave, $nome, $cadastro]): ?>
            <fieldset class="col-md-6">
                <legend class="form-label fs-6"><?= $rotulo ?></legend>
                <div class="border rounded p-2 lista-opcoes<?= isset($erros[$campo]) ? ' border-danger' : '' ?>">
                    <?php foreach ($opcoes as $opcao): ?>
                        <?php $id = "{$campo}-{$opcao[$chave]}"; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="<?= $this->e($id) ?>"
                                   name="<?= $campo ?>[]" value="<?= $this->e($opcao[$chave]) ?>"
                                <?= in_array((string) $opcao[$chave], $livro[$campo], true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="<?= $this->e($id) ?>">
                                <?= $this->e($opcao[$nome]) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($opcoes === []): ?>
                        <a class="small" href="<?= $cadastro ?>">Nenhum cadastrado. Cadastrar agora.</a>
                    <?php endif; ?>
                </div>
                <?php if (isset($erros[$campo])): ?>
                    <div class="invalid-feedback d-block"><?= $this->e($erros[$campo]) ?></div>
                <?php endif; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>
    <div class="card-footer bg-transparent d-flex gap-2">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="/livros">Cancelar</a>
    </div>
</form>
