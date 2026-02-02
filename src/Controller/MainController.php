<?php

namespace App\Controller;

use App\Form\PokemonSearchType;
use App\Form\PokemonTeamBuildType;
use App\Service\Pokeapi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/{_locale}', name: 'index', defaults:['_locale' => 'fr'])]
    public function index(Pokeapi $pokeapi, Request $request) 
    {
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
    public function pokemon(Pokeapi $pokeapi, string $name, Request $request) 
    {
        
        $pokemon = $pokeapi->pokemonGetSingle($name);
        if(isset($pokemon['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemon['message']
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
        return $this->render('main/pokemon.html.twig', [
            'pokemon' => $pokemon,
            'form' => $form
        ]);
    }



    // ROUTE POUR LE TEAM BUILD
    #[Route('/{_locale}/teambuild', name: 'teambuild', defaults:['_locale' => 'fr'])]
    public function teambuild() 
    {
        $form = $this->createForm(PokemonTeamBuildType::class);

        return $this->render('main/teambuild.html.twig', [
            'form' => $form
        ]);
    }

    // ROUTE AJAX
    #[Route('/{_locale}/teambuild/pokemon/{name}', name: 'teambuild_pokemon', defaults:['_locale' => 'fr'])]
    public function teambuildPokemon(Pokeapi $pokeapi, string $name) 
    {       
        $id = $pokeapi->nameFrtoEn($name);
        if(!$id) {
            return $this->render('error.html.twig', [
                'message' => 'Pokémon introuvable'
            ]);
        }       
        $pokemon = $pokeapi->pokemonGetSingle($id); 
        if(isset($pokemon['error'])) {
            return $this->render('error.html.twig', [
                'message' => $pokemon['message']
            ]);
        }
        return $this->render('main/_teambuildPokemon.html.twig', [
            'pokemon' => $pokemon
        ]);
    }

    #[Route('/{_locale}/table_des_types', name: 'table_type', defaults:['_locale' => 'fr'])]
    public function tableType() 
    {
        return $this->render('main/tableType.html.twig');
    }

}