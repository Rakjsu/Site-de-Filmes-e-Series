<?php
use PHPUnit\Framework\TestCase;

class ApiMarkersTest extends TestCase
{
    public function testGetMarkersEndpoint()
    {
        $url = 'http://localhost/api/get-markers.php?contentId=movie1&contentType=movie';
        $response = file_get_contents($url);
        $this->assertNotFalse($response, 'A resposta da API não pode ser falsa');
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'A resposta deve ser um array JSON');
        $this->assertArrayHasKey('markers', $json, 'Deve conter a chave markers');
    }
} 