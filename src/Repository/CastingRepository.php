<?php

namespace App\Repository;

use App\Entity\Movie;
use App\Entity\Casting;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Casting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Casting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Casting[]    findAll()
 * @method Casting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CastingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Casting::class);
    }

    /**
     * Exemple de requête de jointure
     *
     * SELECT * FROM casting
        INNER JOIN movie ON casting.movie_id = movie.id
        INNER JOIN person ON casting.person_id = person.id
        WHERE movie_id = 1
     */
    public function getCastingsJoinedToPersonByMovie(Movie $movie)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT c, p FROM App\Entity\Casting c 
            INNER JOIN c.person p
            WHERE c.movie = :movie
            ORDER BY c.creditOrder ASC'
        )->setParameter('movie', $movie);

        return $query->getResult();
    }

    /**
     * Idem en Query Builder
     */
    public function findAllJoinedToPersonByMovie(Movie $movie)
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.person', 'p')
            ->addSelect('p')
            // Cf PDO requêtes préparées
            // :movie = le paramètre à "sécuriser"
            ->where('c.movie = :movie')
            ->orderBy('c.creditOrder', 'ASC')
            // On demande de remplacer :movie par notre valeur $movie
            ->setParameter('movie', $movie);

        return $qb->getQuery()->getResult();
    }


    // /**
    //  * @return Casting[] Returns an array of Casting objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Casting
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
