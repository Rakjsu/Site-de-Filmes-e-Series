<?php
use PHPUnit\Framework\TestCase;

class ConfiguracoesTest extends TestCase
{
    public function testAlterarConfiguracao()
    {
        $config = ['tema' => 'claro'];
        $config['tema'] = 'escuro';
        $this->assertEquals('escuro', $config['tema']);
    }

    public function testRecuperarConfiguracaoPadrao()
    {
        $config = ['tema' => 'claro'];
        $this->assertEquals('claro', $config['tema']);
    }
} 