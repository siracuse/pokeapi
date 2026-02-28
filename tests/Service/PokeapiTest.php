<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\Pokeapi;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;

class PokeapiTest extends TestCase
{
    
    private Pokeapi $service;

    protected function setUp(): void
    {
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockCache = $this->createMock(CacheInterface::class);

        $this->service = new Pokeapi($mockClient,$mockCache,3600);
    }


    public function testNameSearchToIdValid()
    {
        $result = $this->service->nameSearchToId('pikachu');
        $this->assertSame(25, $result);
    }

    public function testNameSearchToIdSensible()
    {
        $result = $this->service->nameSearchToId('PiKaCHU');
        $this->assertSame(25, $result);
    }

    public function testNameSearchToIdFalse()
    {
        $result = $this->service->nameSearchToId('toto');
        $this->assertNull($result);
    }

    public function testNameSearchToIdCapitalAccents()
    {
        $result = $this->service->nameSearchToId('Évoli');
        $this->assertSame(133, $result);
    }

    public function testNameSearchToIdWithVoid()
    {
        $result = $this->service->nameSearchToId('');
        $this->assertNull($result);
    }

    public function testNameSearchToIdWithSpace()
    {
        $result = $this->service->nameSearchToId('  pikachu  ');
        $this->assertSame(25, $result);
    }


}