<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    public function testIndex200()
    {
        $client = static::createClient();
        $client->request('GET', '/fr'); 

        $this->assertResponseIsSuccessful();
    }

    public function testPokemonPikachu200()
    {
        $client = static::createClient();
        $client->request('GET', '/fr/pokemon/pikachu'); 

        $this->assertResponseIsSuccessful();
    }

    public function testTableTypes200()
    {
        $client = static::createClient();
        $client->request('GET', '/fr/table_des_types'); 

        $this->assertResponseIsSuccessful();
    }

    public function testCobbleverse200()
    {
        $client = static::createClient();
        $client->request('GET', '/fr/cobbleverse'); 

        $this->assertResponseIsSuccessful();
    }
}