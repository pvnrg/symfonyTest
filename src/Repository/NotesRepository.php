<?php

namespace App\Repository;

use App\Entity\Notes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notes>
 *
 * @method Notes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Notes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Notes[]    findAll()
 * @method Notes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notes::class);
    }

//    /**
//     * @return Notes[] Returns an array of Notes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Notes
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByFields($filter, $user_id): array
    {
        $q = $this->createQueryBuilder('n')->where("n.user_id = '".$user_id."'");

        if(!empty($filter['search']))
        {
            $q->andWhere("n.title like '%".$filter['search']."%' OR n.content like '%".$filter['search']."%'"); // OR n.content like '%'".$filter['search']."'%'
        }

        if(!empty($filter['category']))
        {
            $q->andWhere("n.category = '".$filter['category']."'");
        }

        if(!empty($filter['status']))
        {
            $q->andWhere("n.status = '".$filter['status']."'");
        }
        
        $res = $q->orderBy('n.id', 'DESC')
        ->getQuery()->getResult();
        return $res;
    }
}
