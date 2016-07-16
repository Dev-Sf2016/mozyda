<?php

namespace AppBundle\Entity\Repositories;
use AppBundle\AppBundle;
use AppBundle\Entity\Company;
use AppBundle\Entity\Discount;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;

/**
 * AdminRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DiscountRepository extends \Doctrine\ORM\EntityRepository
{

    public function getDiscountsByCompanyId($company){
        return $this->_em->createQueryBuilder()
            ->select('d')
            ->from('AppBundle:Discount', 'd')
            ->where("d.company  = :company")
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $page
     *
     * @return Pagerfanta
     */
    public function getDiscounts($page = 1,$company = null)
    {
        if($company){
            $query = $this->_em->createQueryBuilder()
                ->select('d')
                ->from('AppBundle:Discount', 'd')
                ->where('d.company = :company')
                ->andWhere('d.startDate <= :todaydate')
                ->andWhere('d.endDate >= :todaydate')
                ->setParameter('todaydate', date('Y-m-d'))
                ->setParameter('company', $company)
                ->orderBy('d.id', 'DESC')
                ->getQuery();

        }else {
            $query = $this->_em->createQueryBuilder()
                ->select('d')
                ->from('AppBundle:Discount', 'd')
                ->where('d.startDate <= :todaydate')
                ->andWhere('d.endDate >= :todaydate')
                ->setParameter('todaydate', date('Y-m-d'))
                ->orderBy('d.id', 'DESC')
                ->getQuery();
        }
//        $this->getEntityManager()->initializeObject($query->getCompany());
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query, false));
        $paginator->setMaxPerPage(Discount::NUM_ITEMS);
        $paginator->setCurrentPage($page);
        return $paginator;
    }
}
