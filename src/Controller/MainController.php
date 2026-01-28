<?php

namespace App\Controller;

use App\Form\PokemonSearchType;
use App\Service\Pokeapi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/{_locale}', name: 'index', defaults:['_locale' => 'fr'])]
    public function index(Pokeapi $pokeapi, Request $request) {
        
        $limit = 10;
        $offset = 0;

        $pokemons = $pokeapi->pokemonGetAll($limit, $offset);
        
        if(isset($pokemons['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemons['message']
            ]);
        }

        $form = $this->createForm(PokemonSearchType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $nameSearch = $data['search'];
            $id = $pokeapi->nameFrtoEn($nameSearch);
            if(!$id) {
                return $this->render('error.html.twig', [
                'message' => 'Pokémon introuvable'
            ]);
            }
            return $this->redirectToRoute('pokemon', ['name' => $id]);
        }
        
        return $this->render('main/index.html.twig', [
            'pokemons' => $pokemons,
            'form' => $form,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    #[Route('/{_locale}/load-more', name: 'load_more', methods: ['GET'])]
    public function loadMore(Pokeapi $pokeapi, Request $request): Response
    {
        $limit = 10;
        $offset = $request->query->getInt('offset', 0);
        $pokemons = $pokeapi->pokemonGetAll($limit, $offset);
        
        if(isset($pokemons['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemons['message']
            ]);
        }

        return $this->render('main/_pokemon_list.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }

    #[Route('/{_locale}/pokemon/{name}', name: 'pokemon', defaults:['_locale' => 'fr'])]
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