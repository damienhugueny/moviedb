<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Role;
use App\Entity\User;
use App\Entity\Genre;
use App\Entity\Movie;
use App\Entity\Person;
use App\Entity\Casting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Provider\MovieDbProvider;
use App\Service\Slugger;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Cette classe est créée à l'install du bundle
 * La commande php bin/console doctrine:fixtures:load
 * exécute la méthode load() de toute classe
 * qui étend de Fixture
 *
 * On peut créer ce genre de classe via console make:fixtures
 */
class AppFixtures extends Fixture
{
    /**
     * Pour récupérer le service d'encodage du mot de passe
     */
    private $encoder;

    private $slugger;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder, Slugger $slugger)
    {
        $this->encoder = $userPasswordEncoder;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager)
    {
        // On récupère un objet Faker
        $faker = Faker\Factory::create();
        // On ajoute notre Provider à Faker
        $faker->addProvider(new MovieDbProvider($faker));
        // On peut définir le point de départ du pseudo-hasard
        // et on aura toujours les mêmes données
        $faker->seed('pouet');

        // Les rôles "en dur"
        $roleUser = new Role();
        $roleUser->setName('ROLE_USER');
        $roleUser->setLabel('Utilisateur');
        $manager->persist($roleUser);

        $roleAdmin = new Role();
        $roleAdmin->setName('ROLE_ADMIN');
        $roleAdmin->setLabel('Administrateur');
        $manager->persist($roleAdmin);

        // Nos users "en dur"
        $admin = new User();
        $admin->setEmail('admin@admin.com');
        $admin->setPassword($this->encoder->encodePassword($admin, 'admin'));
        $admin->addUserRole($roleAdmin);
        $manager->persist($admin);

        $user = new User();
        $user->setEmail('user@user.com');
        $user->setPassword($this->encoder->encodePassword($user, 'user'));
        $user->addUserRole($roleUser);
        $manager->persist($user);

        // On crée toutes les données de manière à pouvoir les relier entre elles
        // donc dans un certain order (Genre avant Movie pour relier Genre à Movie)

        // 10 genres

        // Préparons un tableau pour stocker les genres
        // et y accéder deuis la création des films
        $genresList = [];

        for ($i = 0; $i < 10; $i++) {
            $genre = new Genre();
            $genre->setName($faker->unique()->movieGenre);
            $genre->setCreatedAt(new \DateTime());
            // On persist
            $manager->persist($genre);
            // On stocke (on push) le genre pour usage ultérieur
            $genresList[] = $genre;
        }

        // 20 films

        // Préparons un tableau pour stocker les films
        // et y accéder depuis la création des castings
        $moviesList = [];

        for ($i = 0; $i < 20; $i++) {
            $movie = new Movie();
            $movie->setTitle($faker->unique()->movieTitle);
            $movie->setCreatedAt($faker->dateTime());
            // On crée le slug à partir du titre
            $movie->setSlug($this->slugger->slugify($movie->getTitle()));
            // Associons de 1 à 3 genres au hasard
            for ($g = 0; $g < mt_rand(1, 3); $g++) {
                // On va chercher un genre au hasard dans la liste des genres créée au-dessus
                $randomGenre = $genresList[array_rand($genresList)];
                $movie->addGenre($randomGenre);
            }
            // On ajoute le film à la liste des films
            $moviesList[] = $movie;

            $manager->persist($movie);
        }

        // 200 personnes

        // Préparons un tableau pour stocker les personnes
        // et y accéder depuis la création des castings
        $personsList = [];

        for ($i = 0; $i < 200; $i++) {
            $person = new Person();
            $person->setName($faker->movieRole);
            $person->setCreatedAt(new \DateTime());
            // On persist
            $manager->persist($person);
            // On stocke (on push) la personne pour usage ultérieur
            $personsList[] = $person;
        }

        // 100 castings

        for ($i = 0; $i < 100; $i++) {
            $casting = new Casting();
            $casting->setRole($faker->name);
            $casting->setCreditOrder(mt_rand(1, 50));
            $casting->setCreatedAt(new \DateTime());
            // On va chercher un film au hasard dans la liste des films créée au-dessus
            $randomMovie = $moviesList[array_rand($moviesList)];
            $casting->setMovie($randomMovie);
            // On va chercher une personne au hasard dans la liste des personnes créée au-dessus
            $randomPerson = $personsList[array_rand($personsList)];
            $casting->setPerson($randomPerson);
            // On persist
            $manager->persist($casting);
        }

        // On exécute les requêtes SQL en BDD
        $manager->flush();
    }
}
