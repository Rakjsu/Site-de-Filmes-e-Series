<?php
use PHPUnit\Framework\TestCase;

class EstatisticasTest extends TestCase
{
    public function testTotalUsuarios()
    {
        $usuarios = [1,2,3,4,5];
        $this->assertEquals(5, count($usuarios));
    }

    public function testMediaVisualizacoes()
    {
        $visualizacoes = [100, 200, 300];
        $media = array_sum($visualizacoes) / count($visualizacoes);
        $this->assertEquals(200, $media);
    }
} 