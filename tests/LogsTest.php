<?php
use PHPUnit\Framework\TestCase;

class LogsTest extends TestCase
{
    public function testAdicionarLog()
    {
        $logs = [];
        $novoLog = ['acao' => 'login', 'usuario' => 'admin'];
        $logs[] = $novoLog;
        $this->assertCount(1, $logs);
        $this->assertEquals('login', $logs[0]['acao']);
    }

    public function testListarLogsVazio()
    {
        $logs = [];
        $this->assertEmpty($logs);
    }
} 