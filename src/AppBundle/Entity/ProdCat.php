<?php
namespace AppBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="prod_cat")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ProdCatRepository")
 * @UniqueEntity("id")
 */
class ProdCat
{
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products")
     * @ORM\JoinColumn(name="prod_id", referencedColumnName="id")
     */

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="categories")
     * @ORM\JoinColumn(name="cat_id", referencedColumnName="id")
     */

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="prod_id", type="integer", length=10)
     */
    private $prodId;

    /**
     * @ORM\Column(name="cat_id", type="integer", length=10)
     */
    private $catId;




    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set prodId
     *
     * @param integer $prodId
     *
     * @return ProdCat
     */
    public function setProdId($prodId)
    {
        $this->prodId = $prodId;

        return $this;
    }

    /**
     * Get prodId
     *
     * @return integer
     */
    public function getProdId()
    {
        return $this->prodId;
    }

    /**
     * Set catId
     *
     * @param integer $catId
     *
     * @return ProdCat
     */
    public function setCatId($catId)
    {
        $this->catId = $catId;

        return $this;
    }

    /**
     * Get catId
     *
     * @return integer
     */
    public function getCatId()
    {
        return $this->catId;
    }
}
