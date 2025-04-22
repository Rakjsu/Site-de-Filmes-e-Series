<?php
use PHPUnit\Framework\TestCase;

class FilmesTest extends TestCase
{
    public function testAdicionarFilme()
    {
        $filmes = [];
        $novoFilme = ['titulo' => 'Matrix', 'ano' => 1999];
        $filmes[] = $novoFilme;
        $this->assertCount(1, $filmes);
        $this->assertEquals('Matrix', $filmes[0]['titulo']);
    }

    public function testListarFilmesVazio()
    {
        $filmes = [];
        $this->assertEmpty($filmes);
    }
} 