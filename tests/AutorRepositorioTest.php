<?php
declare(strict_types=1);

namespace Tests;

use App\Autor\AutorRepositorio;
use PHPUnit\Framework\TestCase;

final class AutorRepositorioTest extends TestCase
{
    private AutorRepositorio $autores;

    protected function setUp(): void
    {
        $this->autores = new AutorRepositorio(BancoDeTeste::recriar());
    }

    public function testCriarEBuscar(): void
    {
        $autor = $this->autores->criar(['Nome' => 'Fulano']);

        self::assertSame(['CodAutor' => 1, 'Nome' => 'Fulano'], $autor);
        self::assertSame($autor, $this->autores->buscar(1));
    }

    public function testNomeExistente(): void
    {
        $this->autores->criar(['Nome' => 'Fulano']);

        self::assertTrue($this->autores->nomeExistente('Fulano'));
        self::assertFalse($this->autores->nomeExistente('Ciclano'));
    }

    public function testNomeExistenteIgnoraMaiuscula(): void
    {
        $this->autores->criar(['Nome' => 'Fulano']);

        self::assertTrue($this->autores->nomeExistente('FULANO'));
    }

    public function testNomeExistenteIgnoraProprioCodigo(): void
    {
        $this->autores->criar(['Nome' => 'Fulano']);
        $this->autores->criar(['Nome' => 'Ciclano']);

        self::assertFalse($this->autores->nomeExistente('Fulano', 1));
        self::assertTrue($this->autores->nomeExistente('Fulano', 2));
    }
}
