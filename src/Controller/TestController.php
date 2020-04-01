<?php

namespace App\Controller;

use App\Entity\Casting;
use App\Repository\MovieRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{
    /**
     * @Route("/test/casting/add", name="test_casting_add")
     */
    public function castingAdd(MovieRepository $movieRepository, PersonRepository $personRepository, EntityManagerInterface $em)
    {
        // Notre casting
        $casting = new Casting();

        // On lui associe un film existant
        $movie = $movieRepository->find(3);
        $casting->setMovie($movie);

        // On lui associe une personne existante
        $person = $personRepository->find(1);
        $casting->setPerson($person);

        // On attribue le rôle
        $casting->setRole('Christian Wolff');
        // On attribue l'odre d'apparition dans le générique
        $casting->setCreditOrder(1);
        // Sa date de création
        $casting->setCreatedAt(new \DateTime());

        // Persist et flush
        $em->persist($casting);
        $em->flush();

        // On redirige vers la home
        return $this->redirectToRoute('home');
    }
}
