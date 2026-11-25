<?php
declare(strict_types=1);

namespace App\Nucleo;

abstract readonly class Repositorio
{
    protected const string TABELA = '';
    protected const string PK = 'id';
    protected const string BUSCA = '';

    public function __construct(protected Banco $banco)
    {
    }

    public function listar(int $limite = 20, int $pagina = 1, string $termo = ''): array
    {
        $deslocamento = ($pagina - 1) * $limite;
        [$filtro, $parametros] = $this->filtro($termo);

        return $this->banco->consultar(
            'SELECT * FROM ' . static::TABELA . $filtro
            . ' ORDER BY ' . static::PK . ' DESC'
            . " LIMIT {$limite} OFFSET {$deslocamento}",
            $parametros,
        );
    }

    public function contar(string $termo = ''): int
    {
        [$filtro, $parametros] = $this->filtro($termo);
        $sql = 'SELECT COUNT(*) AS total FROM ' . static::TABELA . $filtro;

        return (int) $this->banco->consultar($sql, $parametros)[0]['total'];
    }

    public function buscar(int|string $id): ?array
    {
        $sql = 'SELECT * FROM ' . static::TABELA . ' WHERE ' . static::PK . ' = ?';

        return $this->banco->consultar($sql, [$id])[0] ?? null;
    }

    public function criar(array $dados): array
    {
        $colunas = implode(', ', array_keys($dados));
        $marcas = implode(', ', array_fill(0, count($dados), '?'));

        $this->banco->executar(
            'INSERT INTO ' . static::TABELA . " ({$colunas}) VALUES ({$marcas})",
            array_values($dados),
        );

        return $this->buscar($dados[static::PK] ?? $this->banco->ultimoId());
    }

    public function alterar(int|string $id, array $dados): ?array
    {
        $campos = implode(' = ?, ', array_keys($dados)) . ' = ?';

        $this->banco->executar(
            'UPDATE ' . static::TABELA . " SET {$campos} WHERE " . static::PK . ' = ?',
            [...array_values($dados), $id],
        );

        return $this->buscar($id);
    }

    public function excluir(int|string $id): void
    {
        $this->banco->executar('DELETE FROM ' . static::TABELA . ' WHERE ' . static::PK . ' = ?', [$id]);
    }

    protected function filtro(string $termo): array
    {
        if ($termo === '' || static::BUSCA === '') {
            return ['', []];
        }

        return [' WHERE ' . static::BUSCA . ' LIKE ?', [$this->padrao($termo)]];
    }

    protected function padrao(string $termo): string
    {
        return '%' . addcslashes($termo, '%_\\') . '%';
    }
}
