<?php

namespace App\Controller\Frontend;

use App\Entity\Movie;
use App\Repository\CastingRepository;
use App\Repository\MovieRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home(MovieRepository $movieRepository, Request $request)
    {
        // Y'a-t-il une recherche dans l'URL ?
        $search = $request->query->get('search');
        // On va chercher les films avec ou sans recherche
        $movies = $movieRepository->getMoviesOrderedByTitleAscQb($search);
        
        return $this->render('frontend/main/home.html.twig', [
            'movies' => $movies,
        ]);
    }

    /**
     * @Route("/movie/{slug<[a-z0-9-]+>}", name="movie_show")
     * 
     * Le ParamConverter permet de convertir notre {slug} en objet $movie
     * 
     * Avec le ParamConverter, si on veut récupérer la main sur la 404
     * on doit définir la valeur de $movie à null par défaut dans la méthode movieShow()
     */
    public function movieShow(Movie $movie = null, CastingRepository $castingRepository)
    {
        if ($movie === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce film n\'existe pas.');
        }
        
       // dump($movie);

        $castings = $castingRepository->getCastingsJoinedToPersonByMovie($movie);
       // dump($castings);

        return $this->render('frontend/main/movie_show.html.twig', [
            'movie' => $movie,
            'castings' => $castings,
        ]);
    }
}
