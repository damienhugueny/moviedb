<?php

namespace App\Command;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MovieGetpostersCommand extends Command
{
    protected static $defaultName = 'app:movie:getposters';

    private $movieRepository;
    private $entityManager;

    /**
     * On récupère nos services via le constructeur
     * (car la commande est elle aussi un service)
     */
    public function __construct(MovieRepository $movieRepository, EntityManagerInterface $entityManager)
    {
        $this->movieRepository = $movieRepository;
        $this->entityManager = $entityManager;

        // On appelle le constructeur du parent qui contient du code
        // si non exécuté => bug
        parent::__construct();
    }

    /**
     * Configuration de la commande
     */
    protected function configure()
    {
        $this
            ->setDescription('Download movie poster from OMDBAPI')
            ->addOption('dump', 'd', InputOption::VALUE_NONE, 'Display movie information')
        ;
    }

    /**
     * Que fait la commande
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Listing all movies');

        // 1. Aller chercher les films depuis la BDD
        $movies = $this->movieRepository->findAll();

        // 2. Parcourir chaque film
        foreach ($movies as $movie) {
            // 3. Pour chaque film, lire le JSON depuis OMDBAPI avec la clé
            // 4. Lire l'attribut "Poster"
            $url = $this->getPosterUrlFromMovie($movie);

            // 5. Télécharger l'image en local (dans le dossier public par ex.)
            if ($url !== null) {
                $filename = $this->downloadFromUrl($url, $movie->getId());
            } else {
                $filename = null;
            }
            // 6. Mettre à jour l'entité $movie avec son nom d'image
            $movie->setPoster($filename);

            // Dump
            if ($input->getOption('dump')) {
                $io->text($movie->getTitle() . ' image=' . $filename);
            }
        }
        // On flush tous les films
        $this->entityManager->flush();

//        $io->success('Posters downloaded');

        return 0;
    }

    /**
     * Récupère l'URL du Poster
     *
     * @param Movie $movie Le film concerné
     *
     * @return string|null null ou l'URL du poster du film recherché
     */
    public function getPosterUrlFromMovie(Movie $movie) : ?string
    {
        // urlencode() permet d'encoder une chaine au format URL
        $titleToSearch = urlencode($movie->getTitle());
        // On crée l'URL de destination du JSON à chercher
        $url = "http://www.omdbapi.com/?t={$titleToSearch}&apikey=bbac9560";

        // On va lire le contenu via
        // https://www.php.net/manual/fr/function.file-get-contents.php
        $responseContent = file_get_contents($url);
        // On decode ce contenu en JSON
        // JSON => PHP Object
        $json = json_decode($responseContent);
        // Si pas de résultat
        // OU (si résultat ET poster non disponible)
        // => null

        // Ecriture alternative
        // $cond1 = $json->Response == 'False';
        // $cond2 = $json->Response == 'True' && $json->Poster == 'N/A';
        // if ($cond1 || $cond2)

        if ($json->Response == 'False' || ($json->Response == 'True' && $json->Poster == 'N/A')) {
            return null;
        }
        // Sinon, retourne l'URL du poster
        return $json->Poster;
    }

    /**
     * Télécharge l'image depuis l'URL
     *
     * @param string $url URL de l'image
     * @param int $movieId ID du film en BDD
     *
     * @return string Nom du fichier image téléchargé
     */
    public function downloadFromUrl(string $url, int $movieId) : string
    {
        // On récupère le contenu de l'image
        $image = file_get_contents($url);
        // Extension du fichier
        // cf : https://www.php.net/manual/fr/function.pathinfo
        $pathinfo = pathinfo($url);
        $extension = $pathinfo['extension'];
        // Afin de nommer l'image avec son extension d'origine
        $fileName = 'movie' . $movieId . '.' . $extension;
        // On sauvegarde l'image sur le serveur, si non existante
        // On pourrait avoir une option --overwrite pour gérer ça
        if (!file_exists('public/uploads/posters/' . $fileName)) {
            file_put_contents('public/uploads/posters/' . $fileName, $image);
        }

        return $fileName;
    }
}
