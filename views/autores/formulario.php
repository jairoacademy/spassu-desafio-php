<h1 class="h3 mb-3"><?= $this->e($titulo) ?></h1>

<form class="card border-0 shadow-sm" method="post" action="<?= $this->e($acao) ?>" novalidate>
    <div class="card-body">
        <?= $this->campoToken() ?>
        <label class="form-label" for="nome">Nome</label>
        <input class="form-control<?= isset($erros['nome']) ? ' is-invalid' : '' ?>" id="nome" name="nome"
               value="<?= $this->e($nome) ?>" maxlength="40" required autofocus>
        <?php if (isset($erros['nome'])): ?>
            <div class="invalid-feedback"><?= $this->e($erros['nome']) ?></div>
        <?php endif; ?>
    </div>
    <div class="card-footer bg-transparent d-flex gap-2">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a class="btn btn-outline-secondary" href="/autores">Cancelar</a>
    </div>
</form>
