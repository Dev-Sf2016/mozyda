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
                ORDER BY c.id DESC
            ')

        ;
//        $this->getEntityManager()->initializeObject($query->getCompany());
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(Company::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }

    /**
     * @param Company $company
     * @return Company
     */
    public function persistCompany(Company $company){

        if($company->getId() !== null){
            $this->_em->merge($company);
        }
        else{
            $this->_em->persist($company);
        }

        $this->_em->flush();

        return $company;
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getCompaniesActive($page = 1)
    {
        $query =  $this->getEntityManager()
            ->createQuery('
                SELECT c
                FROM AppBundle:Company c
                where c.isActive = 1
            ')

        ;
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(Company::NUM_ITEMS_FR);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
}
