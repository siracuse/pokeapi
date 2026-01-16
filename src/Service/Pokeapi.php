<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Pokeapi
{

    private HttpClientInterface $client;
    private CacheInterface $cache;

    public function __construct(HttpClientInterface $client, CacheInterface $cache)
    {
        $this->client = $client;
        $this->cache = $cache;
    }

    public function pokemonGetSingle($name): array
    {
        return $this->cache->get('pokemon_' . strtolower($name), function($item) use ($name){
            $item->expiresAfter(3600);
            $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $name);

            $data = $response->toArray();

            $content = [
                'id' => $data['id'],
                'sprite' => $data['sprites']['other']['official-artwork']['front_default'],
                'name' => $data['name'],
                'height' => $data['height'] * 0.1,
                'weight' => $data['weight'] * 0.1,
                'types' => array_map(fn($type) => $type['type']['name'], $data['types']),
                'stats'  => array_map(
                    fn($stat) => [
                        'name' => $stat['stat']['name'],
                        'base_stat' => $stat['base_stat'],
                    ],
                    $data['stats']
                ),
            ];

            return $content;
        });



    }

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

    // Optimisation de perf avec l'asynchrone HttpClient symfony
    // A creuser/comprendre + mise en place du cache
    public function pokemonGetAllv2(int $limit = 0): array
    {
        return $this->cache->get('pokemon_list_' . $limit, function($item) use ($limit) {
            $item->expiresAfter(900);
            $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $limit);
            $pokemonList = $response->toArray()['results'];

            $responses = [];
            foreach ($pokemonList as $pokemon) {
                $responses[] = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $pokemon['name']);
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
        });
    }
}
