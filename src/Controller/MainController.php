<?php

namespace App\Controller;

use App\Form\PokemonSearchType;
use App\Service\Pokeapi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Pokeapi $pokeapi, Request $request) {
        
        $limit = $request->query->getInt('limit', 10);
        $nextLimit = $limit + 10;
        $pokemons = $pokeapi->pokemonGetAllv2($limit);
        
        if(isset($pokemons['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemons['message']
            ]);
        }

        $form = $this->createForm(PokemonSearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $name = $data['search'];
            return $this->redirectToRoute('pokemon', ['name' => $name]);
        }
        return $this->render('main/index.html.twig', [
            'pokemons' => $pokemons,
            'form' => $form,
            'limit' => $limit,
            'nextLimit' => $nextLimit
        ]);
    }



    #[Route('/pokemon/{name}', name: 'pokemon')]
    public function pokemon(Pokeapi $pokeapi, string $name) {
        
        $pokemon = $pokeapi->pokemonGetSingle($name);
        
        if(isset($pokemon['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemon['message']
            ]);
        }
        return $this->render('main/pokemon.html.twig', [
            'pokemon' => $pokemon
        ]);
    }
}
