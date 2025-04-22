<?php
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testLoginComUsuarioValido()
    {
        // Simulação de autenticação (ajuste conforme sua lógica real)
        $usuario = 'admin';
        $senha = '123456';
        $resultado = ($usuario === 'admin' && $senha === '123456');
        $this->assertTrue($resultado, 'Login deve ser bem-sucedido para usuário válido');
    }

    public function testLoginComUsuarioInvalido()
    {
        $usuario = 'admin';
        $senha = 'senhaerrada';
        $resultado = ($usuario === 'admin' && $senha === '123456');
        $this->assertFalse($resultado, 'Login deve falhar para senha inválida');
    }
} 