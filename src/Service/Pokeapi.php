<?php

namespace App\Service;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Pokeapi {

    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    public function pokemonGetSingle($id) : array {
        
        $response = $this->client->request('GET', 'https://pokeapi.co/api/v2/pokemon/'.$id);
        
        $data = $response->toArray();
        
        $content = [
            'id' => $data['id'], 
            'sprite' => $data['sprites']['other']['official-artwork']['front_default'],
            'name' => $data['name'],
            'types' => array_map(fn($type) => $type['type']['name'], $data['types'])
        ];

        return $content;
    }
}