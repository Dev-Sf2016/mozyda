<?php

namespace AppBundle\Entity\Repositories;
use AppBundle\Entity\CompanyDelegate;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * CompanyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyDelegateRepository extends EntityRepository
{

    /**
     * @param $email
     * @return string
     *
     * @return null|CompanyDelegate
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByEmail($email)
    {
        return $this->_em->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:CompanyDelegate', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $company
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findDefaultDelegateByCompany($company){
        return $this->_em->createQueryBuilder()
            ->select('c')
            ->from('AppBundle:CompanyDelegate', 'c')
            ->where('c.company = :company')
            ->andWhere('c.isDefault = 1')
            ->setParameter('company', $company)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param CompanyDelegate $delegate
     * @return CompanyDelegate
     */
    public function persistDelegate($delegate){
        if($delegate->getId() !== null){
            $this->_em->merge($delegate);
        }
        else{
            $this->_em->persist($delegate);
        }

        $this->_em->flush();

        return $delegate;
    }
    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getDelegates($page = 1,$company = null)
    {
        if($company){
            $query = $this->_em->createQueryBuilder()
                ->select('d')
                ->from('AppBundle:CompanyDelegate', 'd')
                ->where('d.company = :company')
                ->andWhere('d.isDefault != 1')
                ->setParameter('company', $company)
                ->orderBy('d.id', 'DESC')
                ->getQuery();

        }else {
            $query = $this->_em->createQueryBuilder()
                ->select('d')
                ->from('AppBundle:CompanyDelegate', 'd')
                ->where('d.isDefault != 1')
                ->orderBy('d.id', 'DESC')
                ->getQuery();
        }
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(CompanyDelegate::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
    
}
