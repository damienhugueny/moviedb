<?php

namespace App\Controller\Backend;

use App\Entity\Movie;
use App\Form\Type\MovieType;
use App\Repository\MovieRepository;
use App\Service\MessageGenerator;
use App\Service\Slugger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MovieController extends AbstractController
{
    /**
     * Lister les films
     *
     * @Route("/backend/movie", name="backend_movie_list", methods={"GET"})
     */
    public function list(MovieRepository $movieRepository)
    {
        $movies = $movieRepository->findBy(
            [],
            ['title' => 'ASC']
        );

        return $this->render('backend/movie/list.html.twig', [
            'movies' => $movies,
        ]);
    }

    /**
     * Ajout d'un film
     *
     * @Route("/backend/movie/add", name="backend_movie_add", methods={"GET", "POST"})
     */
    public function add(Request $request, MessageGenerator $messageGenerator, Slugger $slugger)
    {
        // On crée une nouvelle entité Movie
        $movie = new Movie();

        // On crée le formulaire d'ajout du film
        // ... sur lequel on "map" le film
        $form = $this->createForm(MovieType::class, $movie);

        // On demande au form de "prendre en charge" la requête
        $form->handleRequest($request);

        // Si form est soumis ? Est-il valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // A ce stade l'entité $movie contient déjà toutes les infos du form :)
            // car mappées via le form depuis handleRequest()

            // On sauvegarde le film
            $em = $this->getDoctrine()->getManager();
            $em->persist($movie);
            $em->flush($movie);
            
            $this->addFlash('success', $messageGenerator->getHappyMessage());
            
            // On redirige vers la liste
            return $this->redirectToRoute('backend_movie_list');
        }
        
        return $this->render('backend/movie/add.html.twig', [
            // createView() permet de récupérer
            // la représentation HTML du form
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un film
     *
     * @Route("/backend/movie/edit/{id<\d+>}", name="backend_movie_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id, Movie $movie = null, MessageGenerator $messageGenerator, Slugger $slugger)
    {
        if ($movie === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce film n\'existe pas.');
        }

        // On crée le formulaire d'edition du film
        // ... sur lequel on "map" le film
        $form = $this->createForm(MovieType::class, $movie);

        // On demande au form de "prendre en charge" la requête
        $form->handleRequest($request);

        // Si form est soumis ? Est-il valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // A ce stade l'entité $movie est connue de Doctrine

            // On sauvegarde le film (donc sans persist ici)
            $em = $this->getDoctrine()->getManager();
            $em->flush($movie);

            $this->addFlash('success', $messageGenerator->getHappyMessage());

            // On redirige vers la même page
            return $this->redirectToRoute('backend_movie_edit', ['id' => $id]);
        }
        
        return $this->render('backend/movie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/backend/movie/delete/{id<\d+>}", name="backend_movie_delete", methods={"GET"})
     */
    public function delete(Movie $movie = null)
    {
        if ($movie === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce film n\'existe pas.');
        }

        // On remove via Doctrine Manager
        $em = $this->getDoctrine()->getManager();
        $em->remove($movie);
        $em->flush($movie);

        $this->addFlash('success', 'Film supprimé : '.$movie->getTitle());

        return $this->redirectToRoute('backend_movie_list');
    }

    /**
     * @Route("/backend/movie/{id<\d+>}", name="backend_movie_show", methods={"GET"})
     */
    public function show(Movie $movie = null)
    {
        // si on veut récupérer la main sur la 404
        // = null en valeur par défaut du param
        if ($movie === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce film n\'existe pas.');
        }

        return $this->render('backend/movie/show.html.twig', [
            'movie' => $movie,
        ]);
    }
}
