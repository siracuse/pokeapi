<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Pokeapi
{

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    // public function pokemonGetSingle(int $id) : array {

    //     $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/'.$id);

    //     $data = $response->toArray();

    //     $content = [
    //         'id' => $data['id'], 
    //         'sprite' => $data['sprites']['other']['official-artwork']['front_default'],
    //         'name' => $data['name'],
    //         'types' => array_map(fn($type) => $type['type']['name'], $data['types'])
    //     ];

    //     return $content;
    // }

    public function pokemonGetAll(int $limit): array
    {
        $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $limit);
        $pokemonList = $response->toArray()['results'];
        $contents = [];

        foreach ($pokemonList as $pokemon) {

            $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $pokemon['name']);
            $data = $response->toArray();

            $contents[] = [
                // 'id' => $data['id'],
                'sprite' => $data['sprites']['other']['official-artwork']['front_default'],
                'name' => $data['name'],
                'types' => array_map(fn($type) => $type['type']['name'], $data['types'])
            ];
        }
        return $contents;
    }

    // Optimisation de perf sur l'asynchrone avec HttpClient symfony
    // A creuser/comprendre avant de l'utiliser
    public function pokemonGetAllv2(int $limit): array
    {
        $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $limit);
        $pokemonList = $response->toArray()['results'];

        $responses = [];
        foreach ($pokemonList as $pokemon) {
            $responses[] = $this->client->request('GET','https://pokeapi.co/api/v2/pokemon/' . $pokemon['name']);
        }

        $contents = [];
        foreach ($responses as $response) {
            $data = $response->toArray();

            $contents[] = [
                // 'id'     => $data['id'],
                'sprite' => $data['sprites']['other']['official-artwork']['front_default'],
                'name'   => $data['name'],
                'types'  => array_map(fn($type) => $type['type']['name'], $data['types']),
            ];
        }
        return $contents;
    }
}
