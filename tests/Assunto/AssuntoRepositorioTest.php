<?php
declare(strict_types=1);

namespace Tests\Assunto;

use App\Assunto\AssuntoRepositorio;
use PHPUnit\Framework\TestCase;
use Tests\BancoDeTeste;

final class AssuntoRepositorioTest extends TestCase
{
    private AssuntoRepositorio $assuntos;

    protected function setUp(): void
    {
        $this->assuntos = new AssuntoRepositorio(BancoDeTeste::recriar());
    }

    public function testCriarEBuscar(): void
    {
        $assunto = $this->assuntos->criar(['Descricao' => 'Fulano']);

        self::assertSame(['CodAssunto' => 1, 'Descricao' => 'Fulano'], $assunto);
        self::assertSame($assunto, $this->assuntos->buscar(1));
    }

    public function testDescricaoExistente(): void
    {
        $this->assuntos->criar(['Descricao' => 'Fulano']);

        self::assertTrue($this->assuntos->descricaoExistente('Fulano'));
        self::assertFalse($this->assuntos->descricaoExistente('Ciclano'));
    }

    public function testDescricaoExistenteIgnoraMaiuscula(): void
    {
        $this->assuntos->criar(['Descricao' => 'Fulano']);

        self::assertTrue($this->assuntos->descricaoExistente('FULANO'));
    }

    public function testDescricaoExistenteIgnoraProprioCodigo(): void
    {
        $this->assuntos->criar(['Descricao' => 'Fulano']);
        $this->assuntos->criar(['Descricao' => 'Ciclano']);

        self::assertFalse($this->assuntos->descricaoExistente('Fulano', 1));
        self::assertTrue($this->assuntos->descricaoExistente('Fulano', 2));
    }
}
