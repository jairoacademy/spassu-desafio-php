<form class="row g-2 mb-3" method="get" action="<?= $this->e($rota) ?>" role="search">
    <div class="col">
        <label class="visually-hidden" for="busca"><?= $this->e($placeholder) ?></label>
        <input class="form-control" type="search" id="busca" name="q" maxlength="100"
               placeholder="<?= $this->e($placeholder) ?>" value="<?= $this->e($termo) ?>">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary" type="submit">Filtrar</button>
        <?php if ($termo !== ''): ?>
            <a class="btn btn-link" href="<?= $this->e($rota) ?>">Limpar</a>
        <?php endif; ?>
    </div>
</form>
