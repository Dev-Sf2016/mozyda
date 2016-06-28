<?php

namespace AppBundle\Entity\Repositories;

use AppBundle\Entity\Company;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class CompanyRepository extends EntityRepository
{
    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getCompanies($page = 1)
    {
        $query =  $this->getEntityManager()
            ->createQuery('
                SELECT c
                FROM AppBundle:Company c
            ')

        ;
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(Company::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
}
