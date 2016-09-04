<?php
/**
 * Created by PhpStorm.
 * User: s.aman
 * Date: 8/21/16
 * Time: 10:58 AM
 */

namespace AppBundle\Rest;


class PaginatedCollection
{
    private $_links = [];
    private $items;
    private $total;
    private $count;

    public function __construct(array $items, $totalItems)
    {
        $this->items = $items;
        $this->total = $totalItems;
        $this->count = count($items);

    }

    /**
     * @param $ref
     * @param $url
     * @return PaginationCollection
     */
    public function addLink($ref, $url){
        $this->_links[$ref] = $url;
        return $this;
    }
}