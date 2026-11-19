<h1 class="h3 mb-3"><?= $this->e($titulo) ?></h1>

<form class="card border-0 shadow-sm" method="post" action="<?= $this->e($acao) ?>" novalidate>
    <div class="card-body">
        <?= $this->campoToken() ?>
        <label class="form-label" for="descricao">Descrição</label>
        <input class="form-control<?= isset($erros['descricao']) ? ' is-invalid' : '' ?>"
               id="descricao" name="descricao" value="<?= $this->e($descricao) ?>" maxlength="20" required autofocus>
        <?php if (isset($erros['descricao'])): ?>
            <div class="invalid-feedback"><?= $this->e($erros['descricao']) ?></div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-transparent d-flex gap-2">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="/assuntos">Cancelar</a>
    </div>
</form>
