<?php
use PHPUnit\Framework\TestCase;

class SeriesTest extends TestCase
{
    public function testAdicionarSerie()
    {
        $series = [];
        $novaSerie = ['titulo' => 'Breaking Bad', 'temporadas' => 5];
        $series[] = $novaSerie;
        $this->assertCount(1, $series);
        $this->assertEquals('Breaking Bad', $series[0]['titulo']);
    }

    public function testListarSeriesVazio()
    {
        $series = [];
        $this->assertEmpty($series);
    }
} 