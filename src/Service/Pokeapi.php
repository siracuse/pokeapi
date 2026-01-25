<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Pokeapi
{

    public function __construct(private HttpClientInterface $client, private CacheInterface $cache, private int $pokemonCacheTtl) 
    {}

    public function pokemonGetSingle(string $name): array
    {
        return $this->cache->get('pokemon_' . strtolower($name), function($item) use ($name){
            try {
                $item->expiresAfter($this->pokemonCacheTtl);
                
                $pokemonResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . strtolower($name));
                // $pokemonSpeciesResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon-species/' . strtolower($name));

                $pokemon = $pokemonResponse->toArray();
                // $pokemonSpecies = $pokemonSpeciesResponse->toArray();
                
                
                // $nameFr = $this->getTranslateName($pokemonSpecies['names'], 'fr');

                $content = [
                    'sprite' => $pokemon['sprites']['other']['official-artwork']['front_default'],
                    'name' => $pokemon['name'],
                    'height' => $pokemon['height'] * 0.1,
                    'weight' => $pokemon['weight'] * 0.1,
                    'types' => array_map(fn($type) => $type['type']['name'], $pokemon['types']),
                    'stats'  => array_map(
                        fn($stat) => [
                            'name' => $stat['stat']['name'],
                            'base_stat' => $stat['base_stat'],
                        ],
                        $pokemon['stats']
                    )
                ];
                return $content;
            } catch (\Throwable) {
                $item->expiresAfter(0);
                return ['error' => true, 'message' => 'Impossible de récupérer ce Pokémon pour le moment.'];
            }
        });
    }

    public function pokemonGetAll(int $limit): array
    {
        return $this->cache->get('pokemon_list_' . $limit, function($item) use ($limit) {
            try {
                $item->expiresAfter($this->pokemonCacheTtl);
                
                $pokemonListResponse = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon?limit=' . $limit);
                $pokemonList = $pokemonListResponse->toArray();

                $pokemonResponses = [];
                // $pokemonSpeciesResponses = [];
                
                foreach ($pokemonList['results'] as $pokemon) {
                    $pokemonResponses[] = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/' . $pokemon['name']);
                    // $pokemonSpeciesResponses[] = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon-species/' . $pokemon['name']);
                }   

                $contents = [];
                foreach ($pokemonResponses as $pokemonResponse) {
                    $pokemon = $pokemonResponse->toArray();
                    // $pokemonSpecies = $pokemonSpeciesResponses[$index]->toArray();
                    
                    // $nameFr = $this->getTranslateName($pokemonSpecies['names'], 'fr');
  
                    $contents[] = [
                        'sprite' => $pokemon['sprites']['other']['official-artwork']['front_default'],
                        'name'   => $pokemon['name'],
                        // 'nameEn'   => $pokemon['name'],
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

    // private function getTranslateName(array $names, string $locale) 
    // {
    //     foreach ($names as $entry) {
    //         if ($entry['language']['name'] === $locale) {
    //             return $entry['name'];
    //         }
    //     }
    //     return null;
    // }
}