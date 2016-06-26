<?php

namespace AppBundle\Entity\Repositories;

use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        $user =  $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
//        echo "user is <pre>"; print_r($user); echo "</pre>"; die('--');
        return $user;
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getVendors($page = 1)
    {
        $query =  $this->getEntityManager()
            ->createQuery('
                SELECT u
                FROM AppBundle:User u
                where u.userType  = 2
               
            ')

        ;
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(user::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getCustomers($page = 1)
    {
        $query =  $this->getEntityManager()
            ->createQuery('
                SELECT u
                FROM AppBundle:User u
                where u.userType  = 3
               
            ')

        ;
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(user::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
}
