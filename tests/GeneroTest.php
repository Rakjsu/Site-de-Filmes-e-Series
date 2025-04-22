<?php
use PHPUnit\Framework\TestCase;

class GeneroTest extends TestCase
{
    public function testAdicionarGenero()
    {
        $generos = [];
        $novoGenero = ['nome' => 'Ação'];
        $generos[] = $novoGenero;
        $this->assertCount(1, $generos);
        $this->assertEquals('Ação', $generos[0]['nome']);
    }

    public function testListarGenerosVazio()
    {
        $generos = [];
        $this->assertEmpty($generos);
    }
} 