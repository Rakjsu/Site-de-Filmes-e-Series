<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    public function testCadastrarUsuario()
    {
        $usuarios = [];
        $novoUsuario = ['nome' => 'João', 'email' => 'joao@example.com'];
        $usuarios[] = $novoUsuario;
        $this->assertCount(1, $usuarios);
        $this->assertEquals('João', $usuarios[0]['nome']);
    }

    public function testListarUsuariosVazio()
    {
        $usuarios = [];
        $this->assertEmpty($usuarios);
    }
} 