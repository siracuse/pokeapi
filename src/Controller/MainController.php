<?php

namespace App\Controller;

use App\Service\Pokeapi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Pokeapi $pokeapi) {
        $pokemons = $pokeapi->pokemonGetAllv2(9);
      
        return $this->render('main/index.html.twig', [
            'pokemons' => $pokemons
        ]);
    }

    #[Route('/pokemon/{name}', name: 'pokemon')]
    public function pokemon(Pokeapi $pokeapi, $name) {
        $pokemon = $pokeapi->pokemonGetSingle($name);
      
        return $this->render('main/pokemon.html.twig', [
            'pokemon' => $pokemon
        ]);
    }
}
