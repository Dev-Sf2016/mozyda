<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 8/21/16
 * Time: 1:33 PM
 */

namespace AppBundle\Rest\Factory;


use AppBundle\Rest\PaginatedCollection;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    private $router;
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param QueryBuilder $qb
     * @param Request $request
     * @param $route
     * @param array $routeParams
     * @return PaginatedCollection
     */
    public function createCollection(QueryBuilder $qb, Request $request, $route, array $routeParams = array())
    {
        $page = $request->query->get('page', 1);
        $adapter = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(2);
        $pager->setCurrentPage($page);
        $items = [];
        foreach ($pager->getCurrentPageResults() as $result) {
            $items[] = $result;
        }
        $paginatedCollection = new PaginatedCollection($items, $pager->getNbResults());
        $createLinkUrl = function($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pager->getNbPages()));
        if ($pager->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pager->getNextPage()));
        }
        if ($pager->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pager->getPreviousPage()));
        }
        return $paginatedCollection;
    }

}