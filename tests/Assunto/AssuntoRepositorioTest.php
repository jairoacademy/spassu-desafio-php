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
        $assunto = $this->assuntos->criar(['Descricao' => 'Romance']);

        self::assertSame(['CodAssunto' => 1, 'Descricao' => 'Romance'], $assunto);
        self::assertSame($assunto, $this->assuntos->buscar(1));
    }

    public function testDescricaoExistente(): void
    {
        $this->assuntos->criar(['Descricao' => 'Romance']);

        self::assertTrue($this->assuntos->descricaoExistente('Romance'));
        self::assertFalse($this->assuntos->descricaoExistente('Poesia'));
    }

    public function testDescricaoExistenteIgnoraMaiuscula(): void
    {
        $this->assuntos->criar(['Descricao' => 'Romance']);

        self::assertTrue($this->assuntos->descricaoExistente('ROMANCE'));
    }

    public function testDescricaoExistenteIgnoraProprioCodigo(): void
    {
        $this->assuntos->criar(['Descricao' => 'Romance']);
        $this->assuntos->criar(['Descricao' => 'Poesia']);

        self::assertFalse($this->assuntos->descricaoExistente('Romance', 1));
        self::assertTrue($this->assuntos->descricaoExistente('Romance', 2));
    }
}
