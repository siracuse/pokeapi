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

    private function getTranslateName(array $names, string $locale) {
        foreach ($names as $entry) {
            if ($entry['language']['name'] === $locale) {
                return $entry['name'];
            }
        }
        return null;
    }

    public function pokemonGetSingle(string $name): array
    {
        return $this->cache->get('pokemon_' . strtolower($name), function($item) use ($name){
            try {
                $item->expiresAfter(3600);
                $pokemonResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . strtolower($name));
                $pokemonSpeciesResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon-species/' . strtolower($name));

                $pokemon = $pokemonResponse->toArray();
                $pokemonSpecies = $pokemonSpeciesResponse->toArray();
                
                $nameFr = $this->getTranslateName($pokemonSpecies['names'], 'fr');

                $content = [
                    // 'id' => $pokemon['id'],
                    'sprite' => $pokemon['sprites']['other']['official-artwork']['front_default'],
                    'name' => $nameFr ?? $pokemon['name'],
                    'height' => $pokemon['height'] * 0.1,
                    'weight' => $pokemon['weight'] * 0.1,
                    'types' => array_map(fn($type) => $type['type']['name'], $pokemon['types']),
                    'stats'  => array_map(
                        fn($stat) => [
                            'name' => $stat['stat']['name'],
                            'base_stat' => $stat['base_stat'],
                        ],
                        $pokemon['stats']
                    ),
                ];
                return $content;
            } catch (\Throwable) {
                $item->expiresAfter(0);
                return ['error' => true, 'message' => 'Impossible de récupérer ce Pokémon pour le moment.'];
            }
        });
    }

    public function pokemonGetAllv2(int $limit): array
    {
        return $this->cache->get('pokemon_list_' . $limit, function($item) use ($limit) {
            try {
                $item->expiresAfter(3600);
                
                $pokemonListResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $limit);
                $pokemonList = $pokemonListResponse->toArray();

                $pokemonResponses = [];
                $pokemonSpeciesResponses = [];
                
                foreach ($pokemonList['results'] as $pokemon) {
                    $pokemonResponses[] = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $pokemon['name']);
                    $pokemonSpeciesResponses[] = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon-species/' . $pokemon['name']);
                }   

                $contents = [];
                foreach ($pokemonResponses as $index => $pokemonResponse) {
                    $pokemon = $pokemonResponse->toArray();
                    $pokemonSpecies = $pokemonSpeciesResponses[$index]->toArray();
                    
                    $nameFr = $this->getTranslateName($pokemonSpecies['names'], 'fr');
  
                    $contents[] = [
                        // 'id'     => $data['id'],
                        'sprite' => $pokemon['sprites']['other']['official-artwork']['front_default'],
                        'name'   => $nameFr ?? $pokemon['name'],
                        'nameEn'   => $pokemon['name'],
                        'types'  => array_map(fn($type) => $type['type']['name'], $pokemon['types']),
                    ];
                }
                return $contents;
            } catch (\Throwable) {
                $item->expiresAfter(0);
                return ['error' => true, 'message' => 'Impossible de récupérer les Pokémons pour le moment.'];
            }
        });
    }
}